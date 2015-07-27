<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager\OrderCreationManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PayDirectProcessorInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentGroundType;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;

class PayRent
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
     * Runs rent payment.
     *
     * @param  Payment $payment
     * @return Order
     */
    public function executePayment(Payment $payment)
    {
        $this->logger->debug(
            sprintf(
                'Get new order for payment ID %s',
                $payment->getId()
            )
        );
        $order = $this->orderCreationManager->createRentOrder($payment);

        $this->orderStatusManager->setNew($order);

        $this->closePaymentIfOneTime($payment);

        $this->em->flush();

        try {
            if ($this->getPaymentProcessor($payment)->executeOrder(
                $order,
                $payment->getPaymentAccount(),
                PaymentGroundType::RENT
            )) {
                $this->orderStatusManager->setPending($order);
            } else {
                $this->orderStatusManager->setError($order);
            }
        } catch (\Exception $e) {
            $this->logger->alert('Order Error:' .  $e->getMessage());
            $this->orderStatusManager->setError($order);
        }

        if (OrderStatus::ERROR == $order->getStatus()) {
            $this->closePaymentIfRecurring($payment, $order);
        } else {
            $this->setContractAsCurrent($payment->getContract());
        }
        $this->logger->debug(
            sprintf(
                'New order ID %d, status: %s',
                $order->getId(),
                $order->getStatus()
            )
        );

        $this->em->flush();

        return $order;
    }

    /**
     * Finds payment processor for a given payment.
     *
     * @param  Payment                                                   $payment
     * @return SubmerchantProcessorInterface|PayDirectProcessorInterface
     */
    protected function getPaymentProcessor(Payment $payment)
    {
        return $this->paymentProcessorFactory->getPaymentProcessor($payment->getContract()->getGroup());
    }

    /**
     * Closes one-time payment.
     *
     * @param  Payment    $payment
     * @throws \Exception
     */
    protected function closePaymentIfOneTime(Payment $payment)
    {
        if (PaymentTypeEnum::ONE_TIME == $payment->getType() ||
            date('n') == $payment->getEndMonth() && date('Y') == $payment->getEndYear()
        ) {
            $payment->setClosed($this, PaymentCloseReason::EXECUTED);
        }
    }

    /**
     * Closes recurring payment if payment source is credit card.
     *
     * @param  Payment    $payment
     * @param  Order      $order
     * @throws \Exception
     */
    protected function closePaymentIfRecurring(Payment $payment, Order $order)
    {
        if (OrderPaymentType::CARD == $order->getPaymentType() && $payment->isRecurring()) {
            $this->logger->debug(
                'Close CC recurring payment ID ' . $payment->getId() . ' for order ID ' . $order->getId()
            );
            $payment->setClosed($this, PaymentCloseReason::RECURRING_ERROR);
        }
    }

    /**
     * Sets contract status to CURRENT if it was INVITE or APPROVED.
     *
     * @param Contract $contract
     */
    protected function setContractAsCurrent(Contract $contract)
    {
        $status = $contract->getStatus();
        if (in_array($status, [ContractStatus::INVITE, ContractStatus::APPROVED])) {
            $contract->setStatus(ContractStatus::CURRENT);
            $this->em->persist($contract);
        }
    }
}
