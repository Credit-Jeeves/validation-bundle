<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

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
     * @param EntityManager $em
     * @param ExternalApiClientFactory $apiClientFactory
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "apiClientFactory" = @DI\Inject("accounting.api_client.factory")
     * })
     */
    public function __construct(EntityManager $em, ExternalApiClientFactory $apiClientFactory)
    {
        $this->em = $em;
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function openBatch(Order $order)
    {
        if (!$order->getContract() ||
            !$order->getCompleteTransaction() ||
            !($paymentBatchId = $order->getCompleteTransaction()->getBatchId()) ||
            !($settings = $order->getContract()->getHolding()->getAccountingSettings()) ||
            !($paymentProcessor = $this->getPaymentProcessor($order)) ||
            !($apiClient = $this->getApiClient($order, $settings->getApiIntegration()))
        ) {
            return false;
        }

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');

        if ($repo->isOpenedBatch($paymentBatchId, $paymentProcessor, $settings->getApiIntegration())) {
            return true;
        }

        $paymentBatchDate = $order->getCompleteTransaction()->getBatchDate();

        $externalPropertyId = $order
            ->getUnit()
            ->getProperty()
            ->getPropertyMappingByHolding($order->getContract()->getHolding())
            ->getExternalPropertyId();

        $accountingBatchId = $apiClient->openBatch($externalPropertyId, $paymentBatchDate);

        if (!$accountingBatchId) {
            return false;
        }

        $paymentBatchMapping = new PaymentBatchMapping();
        $paymentBatchMapping->setAccountingBatchId($accountingBatchId);
        $paymentBatchMapping->setPaymentBatchId($paymentBatchId);
        $paymentBatchMapping->setAccountingPackageType($settings->getApiIntegration());
        $paymentBatchMapping->setPaymentProcessor($paymentProcessor);

        $this->em->persist($paymentBatchMapping);
        $this->em->flush($paymentBatchMapping);

        return true;
    }

    protected function getPaymentProcessor(Order $order)
    {
        return PaymentProcessor::mapByOrderType($order->getType());
    }

    protected function getApiClient(Order $order, $accountingType)
    {
        if ($order->getContract() && $order->getContract()->getHolding()) {
            return $this
                ->apiClientFactory
                ->setSettings($order->getContract()->getHolding()->getExternalSettings())
                ->createClient($accountingType);
        }

        return null;
    }
}
