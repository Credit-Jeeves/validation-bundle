<?php

namespace RentJeeves\ExternalApiBundle\Services;


use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use JMS\Serializer\Serializer;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use Monolog\Logger;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use RentJeeves\ExternalApiBundle\Services\EmailNotifier\FailedPostPaymentNotifier;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;

/**
 * @DI\Service("accounting.payment_sync")
 */
class AccountingPaymentSynchronizer
{
    // Time limit for executing a transaction into external API
    const MAXIMUM_RUNTIME_SEC = 600; // 10 minutes

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ExternalApiClientFactory
     */
    protected $apiClientFactory;

    /**
     * @var SoapClientFactory
     */
    protected $soapClientFactory;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var FailedPostPaymentNotifier
     */
    protected $notifier;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "apiClientFactory" = @DI\Inject("accounting.api_client.factory"),
     *     "soapClientFactory" = @DI\Inject("soap.client.factory"),
     *     "jms_serializer" = @DI\Inject("jms_serializer"),
     *     "logger" = @DI\Inject("logger"),
     *     "notifier" = @DI\Inject("failed.post.payment.notifier")
     * })
     */
    public function __construct(
        EntityManager $em,
        ExternalApiClientFactory $apiClientFactory,
        SoapClientFactory $soapClientFactory,
        Serializer $serializer,
        Logger $logger,
        FailedPostPaymentNotifier $notifier
    ) {
        $this->em = $em;
        $this->apiClientFactory = $apiClientFactory;
        $this->soapClientFactory = $soapClientFactory;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->notifier = $notifier;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function isAllowedToSend(Order $order)
    {
        $this->logger->debug('Checking if external payment post is allowed and order can be send...');

        if (!$contract = $order->getContract()) {
            $this->logger->debug(
                sprintf('Order ID#%s does not have contract, we don\'t send it to external API', $order->getId())
            );

            return false;
        }
        
        $holding = $contract->getHolding();
        if ($contract->getHolding()->isApiIntegrated() === false) {
            $this->logger->debug(sprintf('This accounting system (%s) not use api.', $holding->getAccountingSystem()));

            return false;
        }

        $accountingSystem = $holding->getAccountingSystem();
        $postAppFeeAndSecurityDeposit = $holding->isPostAppFeeAndSecurityDeposit();
        if ($order->getCustomOperation()) {
            if (false == $postAppFeeAndSecurityDeposit) {
                $this->logger->debug(sprintf(
                    'Order ID#%s with custom operation NOT allowed for external payment post. ' .
                    'Posting AppFee/Security Deposit switched off in holding %s (ID#%s). done.',
                    $order->getId(),
                    $holding->getName(),
                    $holding->getId()
                ));

                return false;
            }
            // RT-1926: Allow only ResMan non rent payments. Other AS will be allowed later.
            if (AccountingSystem::RESMAN !== $accountingSystem) {
                $this->logger->debug(sprintf(
                    'Order ID#%s with custom operation NOT allowed for external payment post. ' .
                    'Api Integration Type of holding %s (ID#%s) is not ResMan. done.',
                    $order->getId(),
                    $holding->getName(),
                    $holding->getId()
                ));

                return false;
            }
        }


        $isIntegrated = (!empty($accountingSystem) && $accountingSystem !== AccountingSystem::NONE);
        if ($isIntegrated && $holding->isAllowedToSendRealTimePayments()) {
            $this->logger->debug('Holding is allowed for external payment post.');
            $group = $contract->getGroup();
            if ($group->isExistGroupSettings() && $group->getGroupSettings()->getIsIntegrated() === true) {
                $this->logger->debug('Group is allowed for external payment post. Post!');

                return true;
            }
            $this->logger->debug('Group is NOT allowed for external payment post. done.');

            return false;
        }
        $this->logger->debug('Holding is NOT allowed for external payment post. done.');

        return false;
    }

    /**
     * @param Order $order
     */
    public function createJob(Order $order)
    {
        $this->logger->debug(sprintf('Order(%s) added to queue for sending to accounting system.', $order->getId()));

        $job = new Job('external_api:payment:push', ['--app=rj']);
        $job->setMaxRuntime(self::MAXIMUM_RUNTIME_SEC);
        $job->addRelatedEntity($order);

        $this->em->persist($job);
        $this->em->flush($job);
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function sendOrderToAccountingSystem(Order $order)
    {
        try {
            if (!$order->hasContract()) {
                // This should be an alert - orders should have associated contracts!
                $this->logger->alert(
                    sprintf(
                        'Order(%s) does not have an associated contract - cannot send to accounting system.',
                        $order->getId()
                    )
                );

                return false;
            }

            if (!$this->isAllowedToSend($order)) {
                // This should not be an alert since we're just checking if we should send.
                $this->logger->debug(
                    sprintf(
                        "Order(%d) should not be sent to the accounting system.",
                        $order->getId()
                    )
                );

                return false;
            }

            $contract = $order->getContract();
            $holding = $contract->getHolding();
            if (!($transaction = $order->getCompleteTransaction() and
                $holding->getExternalSettings() and
                $paymentBatchId = $transaction->getBatchId() and
                $apiClient = $this->getApiClient($holding->getAccountingSystem(), $holding->getExternalSettings()) and
                $this->existsExternalMapping($order, $apiClient)
            )) {
                // This should be an alert - mappings are missing!
                $this->logger->alert(
                    sprintf(
                        'Order(%d) can not be sent to accounting system(%s) - potentially due to missing mappings.',
                        $order->getId(),
                        $holding->getAccountingSystem()
                    )
                );

                return false;
            }

            $this->logger->debug(
                sprintf(
                    'Trying to send order(%d) to accounting system(%s)...',
                    $order->getId(),
                    $holding->getAccountingSystem()
                )
            );

            if ($apiClient->supportsBatches() && !$this->openBatch($order)) {
                throw new \RuntimeException(
                    sprintf('Can\'t open batch on accounting system(%s)', $holding->getAccountingSystem())
                );
            }

            $result = $this->addPaymentToBatch($order);
            $message = sprintf(
                'Order(%d) was sent to accounting system(%s) with result: %s',
                $order->getId(),
                $holding->getAccountingSystem(),
                $result
            );

            if ($result === false) {
                throw new \Exception($message);
            }

            $this->logger->debug($message);

            return true;
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    'Failed posting payment! Exception(%s): "%s" File:%s, Line:%s, Trace:%s',
                    $e->getCode(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                )
            );

            return false;
        }
    }

    /**
     * @param  bool  $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = !!$debug;

        return $this;
    }

    /**
     * @param  Order           $order
     * @param  ClientInterface $apiClient
     * @return bool
     */
    protected function existsExternalMapping(Order $order, ClientInterface $apiClient)
    {
        if ($apiClient->supportsProperties()) {
            $holding = $order->getContract()->getHolding();
            // get property from contract b/c there may be no unit
            if ($property = $order->getContract()->getProperty()) {
                $externalPropertyMapping = $property->getPropertyMappingByHolding($holding);
            }

            if (!empty($externalPropertyMapping) &&
                $externalPropertyId = $externalPropertyMapping->getExternalPropertyId()
            ) {
                return true;
            }
        } else {
            // if apiClient doesn't support properties, we should check unit mapping
            if ($unit = $order->getUnit()) {
                $externalUnitMapping = $unit->getUnitMapping();
            }

            if (!empty($externalUnitMapping) && $externalUnitId = $externalUnitMapping->getExternalUnitId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Order $order
     */
    protected function addPaymentToBatch(Order $order)
    {
        $externalPropertyId = null;
        $holding = $order->getContract()->getHolding();
        $accountingPackageType = $holding->getAccountingSystem();
        $paymentBatchId = $order->getCompleteTransaction()->getBatchId();
        $apiClient = $this->getApiClientByOrder($order);

        if ($apiClient->supportsBatches() && $apiClient->supportsProperties()) {
            $externalPropertyId = $order->getPropertyPrimaryId();
            /** @var PaymentBatchMappingRepository $repo */
            $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');
            $batchId = $repo->getAccountingBatchId(
                $paymentBatchId,
                $accountingPackageType
            );

            $order->setBatchId($batchId);

            return $apiClient->addPaymentToBatch(
                $order,
                $externalPropertyId
            );
        }

        return $apiClient->postPayment(
            $order,
            $externalPropertyId
        );
    }

    /**
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    protected function openBatch(Order $order)
    {
        $transaction = $order->getCompleteTransaction();
        $paymentBatchId = $transaction->getBatchId();
        $accountingType = $this->getAccountingType($order);
        $externalPropertyId = $order->getPropertyPrimaryId();

        // Should open transaction before lock because
        // when we try to flush record Doctrine2 open transaction and all locks for tables get broken
        $this->em->beginTransaction();
        try {
            /** @var PaymentBatchMappingRepository $repo */
            $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');

            $repo->lockTable();

            if ($repo->isOpenedBatch($paymentBatchId, $accountingType)) {
                $this->em->getConnection()->exec(
                    'UNLOCK TABLES;'
                );
                $this->em->commit();

                return true;
            }

            $paymentBatchDate = new \DateTime();
            $description = sprintf(
                'RentTrack Online Payments Batch #%s',
                $paymentBatchId
            );
            $apiClient = $this->getApiClientByOrder($order);

            $accountingBatchId = $apiClient->openBatch(
                $externalPropertyId,
                $paymentBatchDate,
                $description
            );

            if (!$accountingBatchId) {
                $this->em->getConnection()->exec('UNLOCK TABLES;');
                $this->em->commit();

                return false;
            }

            $paymentBatchMapping = new PaymentBatchMapping();
            $paymentBatchMapping->setAccountingBatchId($accountingBatchId);
            $paymentBatchMapping->setPaymentBatchId($paymentBatchId);
            $paymentBatchMapping->setAccountingPackageType($accountingType);
            $paymentBatchMapping->setExternalPropertyId($externalPropertyId);

            $this->em->persist($paymentBatchMapping);
            $this->em->flush($paymentBatchMapping);
            $this->em->getConnection()->exec('UNLOCK TABLES;');
            $this->em->commit();

            return true;

        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * @param string $accountingType
     */
    public function closeBatches($accountingType)
    {
        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');
        $mappingBatches = $repo->getTodayBatches($accountingType);

        /** @var TransactionRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Transaction');

        foreach ($mappingBatches as $mappingBatch) {
            /** @var PaymentBatchMapping $mappingBatch */
            $holding = $repo->getMerchantHoldingByBatchId($mappingBatch->getPaymentBatchId());
            if (empty($holding)) {
                $this->logger->alert(
                    sprintf('We can\'nt find holding by payment batch ID#%s', $mappingBatch->getPaymentBatchId())
                );

                continue;
            }
            $apiClient = $this->getApiClient($accountingType, $holding->getExternalSettings());

            if (!$apiClient) {
                throw new \LogicException(
                    'Api client is missed. Check accountingType: %s and holdingID: %s.
                     They must have settings by choices type',
                    $accountingType,
                    $holding->getId()
                );
            }

            if (!$holding || $holding->getAccountingSystem() != $accountingType) {
                continue;
            }

            if ($apiClient->closeBatch($mappingBatch->getAccountingBatchId(), $mappingBatch->getExternalPropertyId())) {
                $mappingBatch->setStatus(PaymentBatchStatus::CLOSED);
                $this->em->persist($mappingBatch);
                $this->em->flush();
                $this->logger->debug(
                    sprintf('Batch ID:%s closed, holding#%s', $mappingBatch->getId(), $holding->getId())
                );
            } else {
                $this->logger->alert(
                    sprintf('Batch ID:%s failed to close, holding#%s', $mappingBatch->getId(), $holding->getId())
                );
            }

            $this->notifier->createNotifierAboutFailedPostPaymentJob(
                $holding,
                $mappingBatch->getAccountingBatchId()
            );
        }
    }

    /**
     * @param  Order                      $order
     * @return Interfaces\ClientInterface
     */
    protected function getApiClientByOrder(Order $order)
    {
        $accountingType = $this->getAccountingType($order);

        return $this->getApiClient(
            $accountingType,
            $order->getContract()->getHolding()->getExternalSettings()
        );
    }

    /**
     * @param string $accountingType
     * @param SettingsInterface $accountingSettings
     * @return Interfaces\ClientInterface
     */
    protected function getApiClient($accountingType, SettingsInterface $accountingSettings)
    {
        $apiClient = $this->apiClientFactory->createClient($accountingType, $accountingSettings);
        $apiClient->setDebug($this->debug);

        return $apiClient;
    }

    /**
     * @param  Order  $order
     * @return string
     */
    protected function getAccountingType(Order $order)
    {
        return $order->getContract()->getHolding()->getAccountingSystem();
    }
}
