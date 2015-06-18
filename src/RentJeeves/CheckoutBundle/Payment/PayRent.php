<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentGroundType;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("payment.pay_rent")
 */
class PayRent
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OrderManager
     */
    protected $orderManager;

    /**
     * @var PaymentProcessorFactory
     */
    protected $paymentProcessorFactory;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @DI\InjectParams({
     *     "orderManager" = @DI\Inject("payment_processor.order_manager"),
     *     "logger" = @DI\Inject("logger"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager")
     * })
     */
    public function __construct(OrderManager $orderManager, Logger $logger, EntityManager $em)
    {
        $this->orderManager = $orderManager;
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * Setter injection is used b/c PaymentProcessorFactory doesn't exist when __construct is called.
     *
     * @DI\InjectParams({"factory" = @DI\Inject("payment_processor.factory")})
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
        /** @var Order $order */
        $order = $this->orderManager->createRentOrder($payment);

        $this->closePaymentIfOneTime($payment);

        $this->em->persist($order);
        $this->em->flush();

        try {
            $orderStatus = $this->getPaymentProcessor($payment)->executeOrder(
                $order,
                $payment->getPaymentAccount(),
                PaymentGroundType::RENT
            );
            $order->setStatus($orderStatus);
        } catch (\Exception $e) {
            $this->logger->alert('Order Error:' .  $e->getMessage());
            $order->setStatus(OrderStatus::ERROR);
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
     * @param  Payment                                                               $payment
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface
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
        if (OrderType::HEARTLAND_CARD == $order->getType() && $payment->isRecurring()) {
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
        if (in_array($status, array(ContractStatus::INVITE, ContractStatus::APPROVED))) {
            $contract->setStatus(ContractStatus::CURRENT);
            $this->em->persist($contract);
        }
    }
}
