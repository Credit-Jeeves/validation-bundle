<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
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
            !($settings = $order->getContract()->getHolding()->getAccountingSettings()) ||
            !($paymentProcessor = PaymentProcessor::mapByOrderType($order->getType())) ||
            !($apiClient = $this
                ->apiClientFactory
                ->setSettings($order->getContract()->getHolding()->getExternalSettings())
                ->createClient($settings->getApiIntegration()))
        ) {
            return false;
        }

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:PaymentBatchMapping');

        $paymentBatchId = $order->getCompleteTransaction()->getBatchId();

        if ($repo->isOpenedBatch($paymentBatchId, $paymentProcessor, $settings->getApiIntegration())) {
            return true;
        }

        $paymentBatchDate = $order->getCompleteTransaction()->getBatchDate();

        $externalPropertyId = $order
            ->getUnit()
            ->getProperty()
            ->getPropertyMappingByHolding($order->getContract()->getHolding())
            ->getExternalPropertyId();

        $description = sprintf('Open batch for %s with payment batch id "%s"', $paymentProcessor, $paymentBatchId);

        $response = $apiClient->openBatch($externalPropertyId, $paymentBatchDate, $description);

        if (!$response) {
            return false;
        }

        $accountingBatchId = $response->getBatchId();

        $paymentBatchMapping = new PaymentBatchMapping();
        $paymentBatchMapping->setAccountingBatchId($accountingBatchId);
        $paymentBatchMapping->setPaymentBatchId($paymentBatchId);
        $paymentBatchMapping->setAccountingPackageType($settings->getApiIntegration());
        $paymentBatchMapping->setPaymentProcessor($paymentProcessor);

        $this->em->persist($paymentBatchMapping);
        $this->em->flush($paymentBatchMapping);

        return true;
    }
}
