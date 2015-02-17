<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transaction\ResidentTransactions;
use Monolog\Logger;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;

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
    protected $debug;
	
    /**
     * @param EntityManager $em
     * @param ExternalApiClientFactory $apiClientFactory
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

    public function manageOrderToApi(Order $order)
    {
        try {
            if (!$order->getCompleteTransaction()) {
                $this->logger->addInfo(
                    sprintf(
                        "Order(%s) does not have complite transaction and not be send to api",
                        $order->getId()
                    )
                );
                return false;
            }

            if (!$order->getContract() ||
                !$order->getCompleteTransaction() ||
                !($paymentBatchId = $order->getCompleteTransaction()->getBatchId()) ||
                !($settings = $order->getContract()->getHolding()->getAccountingSettings()) ||
                !($paymentProcessor = $this->getPaymentProcessor($order)) ||
                !($apiClient = $this->getApiClient($order, $settings->getApiIntegration()))
            ) {
                $this->logger->addInfo(
                    sprintf(
                        "Order(%s) does not have complite transaction and not be send to api",
                        $order->getId()
                    )
                );
                return false;
            }
            $this->logger->addInfo(
                sprintf(
                    "Have order(%s) which need send to API",
                    $order->getId()
                )
            );
            $this->openBatch($order);
            $result = $this->addPaymentToBatch($order);
            $this->logger->addInfo(
                sprintf(
                    "Order(%s) was sended to API with result: %s",
                    $order->getId(),
                    $result
                )
            );
        } catch (\Exception $e) {
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
        $settings = $order->getContract()->getHolding()->getAccountingSettings();
        $apiClient = $this->getApiClient($order, $settings->getApiIntegration());
        $paymentProcessor = $this->getPaymentProcessor($order);
        $accountingPackageType = $settings->getApiIntegration();
        $paymentBatchId = $order->getCompleteTransaction()->getBatchId();
        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');
        $batchId = $repo->getAccountingBatchId(
            $paymentBatchId,
            $paymentProcessor,
            $accountingPackageType
        );

        $order->setBatchId($batchId);
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

        //print_r($residentTransactionsXml);exit;
        $holding =  $order->getContract()->getGroup()->getHolding();
        $accountId = $holding->getResManSettings()->getAccountId();
        $externalPropertyId = $order->getContract()->getProperty()->getPropertyMappingByHolding($holding);
        $apiClient->setDebug(true);

        return $apiClient->addPaymentToBatch(
            $residentTransactionsXml,
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
            return false;
        }

        $apiClient->setDebug($this->debug);
        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');

        if ($repo->isOpenedBatch($paymentBatchId, $accountingType, $externalPropertyId)) {
            return true;
        }

        $paymentBatchDate = $order->getCompleteTransaction()->getBatchDate();

        $accountingBatchId = $apiClient->openBatch($externalPropertyId, $paymentBatchDate);

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

    protected function getApiClient($accountingType, $accountingSettings)
    {
        return $this
            ->apiClientFactory
            ->setSettings($accountingSettings)
            ->createClient($accountingType);
    }
}
