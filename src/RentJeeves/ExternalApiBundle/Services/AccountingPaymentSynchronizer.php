<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\HeartlandRepository;
use JMS\Serializer\Serializer;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use Monolog\Logger;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use Exception;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
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

    protected $allowedIntegrationApi = [
        ApiIntegrationType::RESMAN,
        ApiIntegrationType::MRI,
        ApiIntegrationType::AMSI,
    ];

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "apiClientFactory" = @DI\Inject("accounting.api_client.factory"),
     *     "soapClientFactory" = @DI\Inject("soap.client.factory"),
     *     "jms_serializer" = @DI\Inject("jms_serializer"),
     *     "exceptionCatcher" = @DI\Inject( "fp_badaboom.exception_catcher"),
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
     * @param Holding $holding
     * @return bool
     */
    public function isAllowedToSend(Holding $holding)
    {
        $accountingSettings = $holding->getAccountingSettings();

        if (empty($accountingSettings)) {
            return false;
        }

        $apiIntegration = $accountingSettings->getApiIntegration();

        if (in_array($apiIntegration, $this->allowedIntegrationApi)) {
            return true;
        }

        return false;
    }

    /**
     * @param Order $order
     * @return bool
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
                $this->logger->debug(
                    sprintf(
                        'Order(%s) not associated with a lease contract don\'t sent to accounting system',
                        $order->getId()
                    )
                );
                return false;
            }

            $holding = $order->getContract()->getHolding();
            if (!$this->isAllowedToSend($holding)) {
                $this->logger->debug(
                    sprintf(
                        "Order(%s) is not allowed to immediately send to accounting system.",
                        $order->getId()
                    )
                );
                return false;
            }

            if (!($transaction = $order->getCompleteTransaction() and
                $holding->getExternalSettings() and
                $paymentBatchId = $transaction->getBatchId() and
                $accountingType = $holding->getAccountingSettings()->getApiIntegration() and
                $apiClient = $this->getApiClient($accountingType, $holding->getExternalSettings()) and
                $this->existsExternalMapping($order, $apiClient)
            )) {
                $this->logger->debug(
                    sprintf(
                        "Order(%s) can not be sent to accounting system(%s)",
                        $order->getId(),
                        $accountingType
                    )
                );

                return false;
            }

            $this->logger->debug(
                sprintf(
                    "Trying to send order(%s) to accounting system(%s)...",
                    $order->getId(),
                    $accountingType
                )
            );
            if ($apiClient->supportsBatches()) {
                $this->openBatch($order);
            }
            $result = $this->addPaymentToBatch($order);
            $message = sprintf(
                "Order(%s) was sent to accounting system (%s) with result: %s",
                $order->getId(),
                $accountingType,
                $result
            );
            $this->logger->debug($message);

            if ($result === false) {
                throw new Exception($message);
            }

            return true;
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logger->addCritical($e->getMessage());

            return false;
        }
    }

    /**
     * @param bool $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = !!$debug;

        return $this;
    }

    /**
     * @param Order $order
     * @param ClientInterface $apiClient
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
        $holding = $order->getContract()->getHolding();
        $settings = $holding->getAccountingSettings();
        $accountingPackageType = $settings->getApiIntegration();
        $externalPropertyId = $order->getPropertyPrimaryID();
        $paymentBatchId = $order->getCompleteTransaction()->getBatchId();
        $apiClient = $this->getApiClientByOrder($order);

        if ($apiClient->supportsBatches()) {
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
     * @param Order $order
     * @return bool
     */
    protected function openBatch(Order $order)
    {
        $transaction = $order->getCompleteTransaction();
        $paymentBatchId = $transaction->getBatchId();
        $accountingType = $this->getAccountingType($order);
        $externalPropertyId = $order->getPropertyPrimaryID();

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');

        if ($repo->isOpenedBatch($paymentBatchId, $accountingType, $externalPropertyId)) {
            return true;
        }

        $paymentBatchDate = new DateTime();
        $description = sprintf(
            'RentTrack Online Payments Batch #%s',
            $paymentBatchId
        );
        $accountingBatchId = $this->getApiClientByOrder($order)->openBatch(
            $externalPropertyId,
            $paymentBatchDate,
            $description
        );

        if (!$accountingBatchId) {
            return false;
        }

        $paymentBatchMapping = new PaymentBatchMapping();
        $paymentBatchMapping->setAccountingBatchId($accountingBatchId);
        $paymentBatchMapping->setPaymentBatchId($paymentBatchId);
        $paymentBatchMapping->setAccountingPackageType($accountingType);
        $paymentBatchMapping->setExternalPropertyId($externalPropertyId);

        $this->em->persist($paymentBatchMapping);
        $this->em->flush($paymentBatchMapping);

        return true;
    }

    public function closeBatches($accountingType)
    {
        $apiClient = $this->getApiClient($accountingType);

        if (!$apiClient) {
            return false;
        }

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');
        $mappingBatches = $repo->getTodayBatches($accountingType);

        /** @var HeartlandRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Heartland');

        foreach ($mappingBatches as $mappingBatch) {
            /** @var PaymentBatchMapping $mappingBatch */
            $holding = $repo->getMerchantHoldingByBatchId($mappingBatch->getPaymentBatchId());
            if (!$holding || $holding->getAccountingSettings()->getApiIntegration() != $accountingType) {
                continue;
            }

            $apiClient->setSettings($holding->getExternalSettings());
            if ($apiClient->closeBatch($mappingBatch->getExternalPropertyId(), $mappingBatch->getAccountingBatchId())) {
                $mappingBatch->setStatus(PaymentBatchStatus::CLOSED);
                $this->em->persist($mappingBatch);
                $this->em->flush();
            }
        }
    }

    /**
     * @param Order $order
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
     * @param $accountingType
     * @param null $accountingSettings
     * @return Interfaces\ClientInterface
     */
    protected function getApiClient($accountingType, $accountingSettings = null)
    {
        /**
         * if we add more than one soap client to AccountingPaymentSynchronizer,
         * we should extract this logic to a different layer
         */
        if (ApiIntegrationType::AMSI == $accountingType) {
            $apiClient = $this->soapClientFactory->getClient(
                $accountingSettings,
                SoapClientEnum::AMSI_LEDGER,
                $this->debug
            );
        } else {
            $apiClient = $this->apiClientFactory->createClient($accountingType);
            $apiClient->setSettings($accountingSettings);
            $apiClient->setDebug($this->debug);
        }

        return $apiClient;
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getAccountingType(Order $order)
    {
        return $order->getContract()->getHolding()->getAccountingSettings()->getApiIntegration();
    }
}
