<?php

namespace RentJeeves\CheckoutBundle\Payment;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorInterface;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

/**
 * @DI\Service("payment_terminal")
 */
class Terminal
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var OrderManager
     */
    protected $orderManager;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var PaymentProcessorFactory
     */
    protected $paymentProcessorFactory;

    /**
     * @param EntityManager $em
     * @param OrderManager $orderManager
     * @param LoggerInterface $logger
     *
     *  @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "orderManager" = @DI\Inject("payment_processor.order_manager"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(EntityManager $em, OrderManager $orderManager, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->orderManager = $orderManager;
        $this->logger = $logger;
    }

    /**
     * @param PaymentProcessorFactory $factory
     *
     * Setter injection is used b/c PaymentProcessorFactory doesn't exist when __construct is called.
     *
     * @DI\InjectParams({"factory" = @DI\Inject("payment_processor.factory")})
     */
    public function setFactory(PaymentProcessorFactory $factory)
    {
        $this->paymentProcessorFactory = $factory;
    }

    /**
     * @param Group $group
     * @param float $amount
     * @param string $descriptor
     * @return Transaction
     */
    public function pay(Group $group, $amount, $descriptor)
    {
        $this->logger->debug(
            sprintf(
                'Trying to get new charge order for group ID %s',
                $group->getId()
            )
        );

        try {
            $order = $this->orderManager->createChargeOrder($group, $amount, $descriptor);

            $this->em->persist($order);
            $this->em->flush();

            $orderStatus = $this->getPaymentProcessor($group)->executeOrder(
                $order,
                $group->getActiveBillingAccount(),
                PaymentGroundType::CHARGE
            );

            $order->setStatus($orderStatus);
        } catch (\Exception $e) {
            $this->logger->alert('VirtualTerminal error occurred:' .  $e->getMessage());
            $order->setStatus(OrderStatus::ERROR);
        }

        $this->logger->debug(
            sprintf(
                'New charge order created! ID %d, status: %s',
                $order->getId(),
                $order->getStatus()
            )
        );

        $this->em->flush();

        return $order->getTransactions()->last();
    }

    /**
     * @param  Group $group
     * @return PaymentProcessorInterface
     */
    protected function getPaymentProcessor(Group $group)
    {
        return $this->paymentProcessorFactory->getPaymentProcessor($group);
    }
}
