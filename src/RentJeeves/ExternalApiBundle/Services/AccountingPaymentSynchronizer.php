<?php

namespace RentJeeves\ExternalApiBundle\Services;

use RentJeeves\DataBundle\Entity\Contract;
use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use JMS\Serializer\Serializer;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use Monolog\Logger;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
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
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var Logger
     */
    protected $logger;

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
     *     "exceptionCatcher" = @DI\Inject("fp_badaboom.exception_catcher"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(
        EntityManager $em,
        ExternalApiClientFactory $apiClientFactory,
        SoapClientFactory $soapClientFactory,
        Serializer $serializer,
        ExceptionCatcher $exceptionCatcher,
        Logger $logger
    ) {
        $this->em = $em;
        $this->apiClientFactory = $apiClientFactory;
        $this->soapClientFactory = $soapClientFactory;
        $this->serializer = $serializer;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->logger = $logger;
    }

    /**
     * @param Contract $contract
     *
     * @return bool
     */
    public function isAllowedToSend(Contract $contract)
    {
        $this->logger->debug('Checking if external payment post is allowed...');
        $holding = $contract->getHolding();
        $integrationType = $holding->getApiIntegrationType();
        $isIntegrated = (!empty($integrationType) && $integrationType !== ApiIntegrationType::NONE);
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
     * @param  Order $order
     * @return bool
     */
    public function sendOrderToAccountingSystem(Order $order)
    {
        try {
            if (!$order->hasContract()) {
                $this->logger->debug(
                    sprintf(
                        'Order(%s) not associated with a lease contract don\'t sent to accounting system',
                        $order->getId()
                    )
                );

                return false;
            }

            $contract = $order->getContract();
            if (!$this->isAllowedToSend($contract)) {
                $this->logger->debug(
                    sprintf(
                        "Order(%d) is not allowed to be sent to accounting system.",
                        $order->getId()
                    )
                );

                return false;
            }

            $holding = $contract->getHolding();
            if (!($transaction = $order->getCompleteTransaction() and
                $holding->getExternalSettings() and
                $paymentBatchId = $transaction->getBatchId() and
                $apiClient = $this->getApiClient($holding->getApiIntegrationType(), $holding->getExternalSettings()) and
                $this->existsExternalMapping($order, $apiClient)
            )) {
                $this->logger->debug(
                    sprintf(
                        'Order(%d) can not be sent to accounting system(%s)',
                        $order->getId(),
                        $holding->getApiIntegrationType()
                    )
                );

                return false;
            }

            $this->logger->debug(
                sprintf(
                    'Trying to send order(%d) to accounting system(%s)...',
                    $order->getId(),
                    $holding->getApiIntegrationType()
                )
            );

            if ($apiClient->supportsBatches() && !$this->openBatch($order)) {
                throw new \RuntimeException(
                    sprintf('Can\'t open batch on accounting system(%s)', $holding->getApiIntegrationType())
                );
            }

            $result = $this->addPaymentToBatch($order);
            $message = sprintf(
                'Order(%d) was sent to accounting system(%s) with result: %s',
                $order->getId(),
                $holding->getApiIntegrationType(),
                $result
            );

            if ($result === false) {
                throw new \Exception($message);
            }

            $this->logger->debug($message);

            return true;
        } catch (\Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logger->addCritical(get_class($e) . ':' . $e->getMessage());

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
            $externalPropertyMapping = $order
                ->getUnit()
                ->getProperty()
                ->getPropertyMappingByHolding($holding);

            if ($externalPropertyMapping && $externalPropertyId = $externalPropertyMapping->getExternalPropertyId()) {
                return true;
            }
        } else {
            // if apiClient doesn't support properties, we should check unit mapping
            $externalUnitMapping = $order
                ->getUnit()
                ->getUnitMapping();
            if ($externalUnitMapping && $externalUnitId = $externalUnitMapping->getExternalUnitId()) {
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
        $accountingPackageType = $holding->getApiIntegrationType();
        $paymentBatchId = $order->getCompleteTransaction()->getBatchId();
        $apiClient = $this->getApiClientByOrder($order);

        if ($apiClient->supportsBatches() && $apiClient->supportsProperties()) {
            $externalPropertyId = $order->getPropertyPrimaryId();
            /** @var PaymentBatchMappingRepository $repo */
            $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');
            $batchId = $repo->getAccountingBatchId(
                $paymentBatchId,
                $accountingPackageType,
                $externalPropertyId
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
     * @param  Order $order
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

            if ($repo->isOpenedBatch($paymentBatchId, $accountingType, $externalPropertyId)) {
                $this->em->getConnection()->exec(
                    'UNLOCK TABLES;'
                );
                $this->em->commit();

                return true;
            }

            $paymentBatchDate = new DateTime();
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
            $apiClient = $this->getApiClient($accountingType, $holding->getExternalSettings());

            if (!$apiClient) {
                throw new \LogicException(
                    'Api client is missed. Check accountingType: %s and holdingID: %s.
                     They must have settings by choices type',
                    $accountingType,
                    $holding->getId()
                );
            }

            if (!$holding || $holding->getApiIntegrationType() != $accountingType) {
                continue;
            }

            if ($apiClient->closeBatch($mappingBatch->getAccountingBatchId(), $mappingBatch->getExternalPropertyId())) {
                $mappingBatch->setStatus(PaymentBatchStatus::CLOSED);
                $this->em->persist($mappingBatch);
                $this->em->flush();
            }
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
        return $order->getContract()->getHolding()->getApiIntegrationType();
    }
}
