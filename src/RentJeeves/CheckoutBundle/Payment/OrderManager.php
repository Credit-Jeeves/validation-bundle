<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\Services\PaidFor;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RuntimeException;

/**
 * @DI\Service("payment_processor.order_manager")
 */
class OrderManager
{
    /**
     * @var PaidFor
     */
    protected $paidFor;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var double
     */
    protected $creditTrackAmount;

    /**
     * @var string
     */
    protected $rtMerchantName;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "paidFor" = @DI\Inject("checkout.paid_for"),
     *     "rtMerchantName" = @DI\Inject("%rt_merchant_name%"),
     *     "amount" = @DI\Inject("%credittrack_payment_per_month%")
     * })
     */
    public function __construct(EntityManager $em, PaidFor $paidFor, $rtMerchantName, $amount)
    {
        $this->em = $em;
        $this->paidFor = $paidFor;
        $this->rtMerchantName = $rtMerchantName;
        $this->creditTrackAmount = $amount;
    }

    /**
     * Creates a new order for rent payment.
     *
     * @param  Payment $payment
     * @return Order
     */
    public function createRentOrder(Payment $payment)
    {
        $order = new Order();
        $paymentAccount = $payment->getPaymentAccount();
        $contract = $payment->getContract();
        $order->setSum($payment->getAmount() + $payment->getOther());
        $order->setUser($paymentAccount->getUser());
        $order->setStatus(OrderStatus::NEWONE);

        $this->createRentOperations($payment, $order);

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($contract->getDepositAccount()->getFeeCC() / 100), 2));
            $order->setType(OrderType::HEARTLAND_CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            if (true === $contract->getDepositAccount()->isPassedAch()) {
                $order->setFee($contract->getDepositAccount()->getFeeACH());
            } else {
                $order->setFee(0);
            }

            $order->setType(OrderType::HEARTLAND_BANK);
        }

        return $order;
    }

    /**
     * Creates a new order for credit track payment.
     *
     * @param  PaymentAccount $paymentAccount
     * @return Order
     */
    public function createCreditTrackOrder(PaymentAccount $paymentAccount)
    {
        $order = new Order();
        $order->setUser($paymentAccount->getUser());
        $order->setSum($this->creditTrackAmount);
        $order->setStatus(OrderStatus::NEWONE);

        /** @var DepositAccount $depositAccount */
        $depositAccount = $this->em->getRepository('DataBundle:Group')
            ->findOneByCode($this->rtMerchantName)
            ->getDepositAccount();

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($depositAccount->getFeeCC() / 100), 2));
            $order->setType(OrderType::HEARTLAND_CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            $order->setFee($depositAccount->getFeeACH());
            $order->setType(OrderType::HEARTLAND_BANK);
        }

        return $order;
    }

    /**
     * Creates rent operations for given payment and order.
     *
     * @param Payment $payment
     * @param Order   $order
     */
    protected function createRentOperations(Payment $payment, Order $order)
    {
        $contract = $payment->getContract();
        $payBalanceOnly = $contract->getGroup()->getGroupSettings()->getPayBalanceOnly();

        if ($payBalanceOnly) {
            $this->createBalanceBasedOperations($payment, $order);
        } else {
            $this->createRegularOperations($payment, $order);
        }
    }

    /**
     * Creates operations if only balance is paid.
     *
     * @param Payment $payment
     * @param Order   $order
     */
    protected function createBalanceBasedOperations(Payment $payment, Order $order)
    {
        $contract = $payment->getContract();

        $paymentAmount = $payment->getTotal();
        $rent = $contract->getRent();
        $paidForDates = array_keys($this->paidFor->getBaseArray($contract));
        if (empty($paidForDates)) {
            throw new RuntimeException('Can not calculate paid_for');
        }
        $paidForCounter = 0;

        do {
            $operationAmount = $paymentAmount >= $rent ? $rent : $paymentAmount;
            $operation = new Operation();
            $operation->setOrder($order);
            $operation->setType(OperationType::RENT);
            $operation->setContract($contract);
            $operation->setAmount($operationAmount);

            if (!isset($paidForDates[$paidForCounter])) {
                $paidFor = new DateTime($paidForDates[$paidForCounter - 1]); // take previous paidFor
                $paidFor->modify('+1 month');
                $paidForDates[$paidForCounter] = $paidFor->format('Y-m-d');
            }

            $paidFor = new DateTime($paidForDates[$paidForCounter]);
            $operation->setPaidFor($paidFor);

            $paymentAmount -= $operationAmount;
            $paidForCounter++;
        } while ($paymentAmount >= $rent);

        if ($paymentAmount > 0) {
            $operation = new Operation();
            $operation->setOrder($order);
            $operation->setType(OperationType::OTHER);
            $operation->setContract($contract);
            $operation->setAmount($paymentAmount);
            $operation->setPaidFor($paidFor);
        }
    }

    /**
     * Creates plain rent operations.
     *
     * @param Payment $payment
     * @param Order   $order
     */
    protected function createRegularOperations(Payment $payment, Order $order)
    {
        $contract = $payment->getContract();

        if ($amount = $payment->getAmount()) {
            $operation = new Operation();
            $operation->setOrder($order);
            $operation->setType(OperationType::RENT);
            $operation->setContract($contract);
            $operation->setAmount($amount);
            $operation->setPaidFor($payment->getPaidFor());
        }
        if ($amount = $payment->getOther()) {
            $operation = new Operation();
            $operation->setOrder($order);
            $operation->setType(OperationType::OTHER);
            $operation->setContract($contract);
            $operation->setAmount($amount);
            $operation->setPaidFor($payment->getPaidFor());
        }
    }
}
