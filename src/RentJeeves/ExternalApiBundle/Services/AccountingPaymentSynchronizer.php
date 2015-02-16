<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;

/**
 * @DI\Service("accounting.payment_sync")
 */
class AccountingPaymentSynchronizer 
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @DI\InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
            !($paymentProcessor = PaymentProcessor::mapByOrderType($order->getType()))
        ) {
            return false;
        }

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

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

        $apiClient = $this
            ->container
            ->get('accounting.api_client.factory')
            ->setSettings($order->getContract()->getHolding()->getExternalSettings())
            ->createClient($settings->getApiIntegration());

        $description = sprintf('Open batch for %s with payment batch id "%s"', $paymentProcessor, $paymentBatchId);

        $response = $apiClient->sendOpenBatch($externalPropertyId, $paymentBatchDate, $description);

        if (!$response) {
            return false;
        }

        $accountingBatchId = $response->getBatchId();

        $paymentBatchMapping = new PaymentBatchMapping();
        $paymentBatchMapping->setAccountingBatchId($accountingBatchId);
        $paymentBatchMapping->setPaymentBatchId($paymentBatchId);
        $paymentBatchMapping->setAccountingPackageType($settings->getApiIntegration());
        $paymentBatchMapping->setPaymentProcessor($paymentProcessor);

        $em->persist($paymentBatchMapping);
        $em->flush($paymentBatchMapping);

        return true;
    }
}
