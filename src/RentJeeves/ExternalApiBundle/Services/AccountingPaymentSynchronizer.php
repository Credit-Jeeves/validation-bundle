<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\HeartlandRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transaction\ResidentTransactions;
use Monolog\Logger;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use Exception;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;

/**
 * @DI\Service("accounting.payment_sync")
 */
class AccountingPaymentSynchronizer
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ExternalApiClientFactory
     */
    protected $apiClientFactory;

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
     *     "jms_serializer" = @DI\Inject("jms_serializer"),
     *     "exceptionCatcher" = @DI\Inject( "fp_badaboom.exception_catcher"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(
        EntityManager $em,
        ExternalApiClientFactory $apiClientFactory,
        Serializer $serializer,
        ExceptionCatcher $exceptionCatcher,
        Logger $logger
    ) {
        $this->em = $em;
        $this->apiClientFactory = $apiClientFactory;
        $this->serializer = $serializer;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->logger = $logger;
    }

    public function sendOrderToAccountingSystem(Order $order)
    {
        try {
            if (!($order->hasContract() and
                $transaction = $order->getCompleteTransaction() and
                $holding = $order->getContract()->getHolding() and
                $holding->getExternalSettings() and
                $paymentBatchId = $transaction->getBatchId() and
                $accountingType = $holding->getAccountingSettings()->getApiIntegration() and
                $externalPropertyId = $order
                    ->getUnit()
                    ->getProperty()
                    ->getPropertyMappingByHolding($holding)
                    ->getExternalPropertyId() and
                $apiClient = $this->getApiClient($accountingType, $holding->getExternalSettings())
            )) {

                if ($order->hasContract()) {
                    $accountingSettings = $order->getContract()->getHolding()->getAccountingSettings();

                    $this->logger->addInfo(
                        sprintf(
                            "Order(%s) can not be sent to accounting system(%s)",
                            $order->getId(),
                            ($accountingSettings) ? $accountingSettings->getApiIntegration() : 'none'
                        )
                    );
                }
                else {
                    $this->logger->addInfo(
                        sprintf(
                            "Order(%s) not associated with a lease contract don't sent to accounting system",
                            $order->getId()
                        )
                    );
                }
                return false;
            }

            $apiClient->setDebug($this->debug);

            $this->logger->addInfo(
                sprintf(
                    "Order(%s) must send to Accounting system(%s)",
                    $order->getId(),
                    $accountingType
                )
            );
            $this->openBatch($order);
            $result = $this->addPaymentToBatch($order);
            $message =  sprintf(
                "Order(%s) was sent to Accounting(%s) system with result: %s",
                $order->getId(),
                $accountingType,
                $result
            );
            $this->logger->addInfo($message);

            if ($result === false) {
                throw new Exception($message);
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logger->addCritical($e->getMessage());
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
     */
    protected function addPaymentToBatch(Order $order)
    {
        $holding = $order->getContract()->getHolding();
        $settings = $holding->getAccountingSettings();
        $accountingPackageType = $settings->getApiIntegration();
        $externalPropertyId = $order->getPropertyPrimaryID();
        $paymentBatchId = $order->getCompleteTransaction()->getBatchId();
        $accountId = $holding->getResManSettings()->getAccountId();

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');
        $batchId = $repo->getAccountingBatchId(
            $paymentBatchId,
            $accountingPackageType,
            $externalPropertyId
        );

        $order->setBatchId($batchId);
        $apiClient = $this->getApiClientByOrder($order);

        return $apiClient->addPaymentToBatch(
            $this->getResidentTransactionXml($order),
            $externalPropertyId,
            $accountId
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

        $accountingBatchId = $this->getApiClientByOrder($order)->openBatch($externalPropertyId, $paymentBatchDate);

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

        $apiClient->setDebug($this->debug);

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
        $apiClient = $this->apiClientFactory->createClient($accountingType);
        if ($apiClient && $accountingSettings) {
            $apiClient->setSettings($accountingSettings);
        }

        return $apiClient;
    }

    /**
     * @param Order $order
     * @return mixed
     */
    protected function getResidentTransactionXml(Order $order)
    {
        $residentTransaction = new ResidentTransactions([$order]);

        $context = new SerializationContext();
        $context->setGroups(['ResMan']);
        $context->setSerializeNull(true);

        $residentTransactionsXml = $this->serializer->serialize(
            $residentTransaction,
            'xml',
            $context
        );

        $residentTransactionsXml = str_replace(
            ['<?xml version="1.0" encoding="UTF-8"?>'],
            '',
            $residentTransactionsXml
        );

        return $residentTransactionsXml;
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
