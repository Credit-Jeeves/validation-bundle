<?php

namespace RentJeeves\CheckoutBundle\Payment;

use Doctrine\ORM\EntityManager;
use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager\OrderCreationManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

class Terminal
{
    /**
     * @var OrderCreationManager
     */
    protected $orderCreationManager;

    /**
     * @var OrderStatusManagerInterface
     */
    protected $orderStatusManager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PaymentProcessorFactory
     */
    protected $paymentProcessorFactory;

    /**
     * @param OrderCreationManager $orderCreationManager
     * @param OrderStatusManagerInterface $orderStatusManager
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderCreationManager $orderCreationManager,
        OrderStatusManagerInterface $orderStatusManager,
        EntityManager $em,
        LoggerInterface $logger
    ) {
        $this->orderCreationManager = $orderCreationManager;
        $this->orderStatusManager = $orderStatusManager;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param PaymentProcessorFactory $factory
     *
     * Setter injection is used b/c PaymentProcessorFactory doesn't exist when __construct is called.
     */
    public function setFactory(PaymentProcessorFactory $factory)
    {
        $this->paymentProcessorFactory = $factory;
    }

    /**
     * @param Group $group
     * @param float $amount
     * @param string $descriptor
     * @throws \Exception
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
            $order = $this->orderCreationManager->createChargeOrder($group, $amount, $descriptor);

            $this->orderStatusManager->setNew($order);

            if ($this->getPaymentProcessor($group)->executeOrder(
                $order,
                $group->getActiveBillingAccount(),
                PaymentGroundType::CHARGE
            )) {
                $this->orderStatusManager->setComplete($order);
            } else {
                $this->orderStatusManager->setError($order);
            }
        } catch (\Exception $e) {
            $this->logger->alert('VirtualTerminal error occurred:' .  $e->getMessage());
            if (!empty($order)) {
                $this->orderStatusManager->setError($order);
            }
            throw $e;
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
     * @return SubmerchantProcessorInterface
     */
    protected function getPaymentProcessor(Group $group)
    {
        return $this->paymentProcessorFactory->getPaymentProcessor($group);
    }
}
