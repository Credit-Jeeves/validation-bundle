<?php
namespace RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderFactory;
use RentJeeves\CheckoutBundle\Services\PaidFor;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Model\GroupSettings;
use RuntimeException;

class OrderCreationManager
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
    protected $rtGroupCode;

    /**
     * @param EntityManager $em
     * @param PaidFor $paidFor
     * @param $rtGroupCode
     * @param $amount
     */
    public function __construct(EntityManager $em, PaidFor $paidFor, $rtGroupCode, $amount)
    {
        $this->em = $em;
        $this->paidFor = $paidFor;
        $this->rtGroupCode = $rtGroupCode;
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
        $order = OrderFactory::getOrder($payment->getContract()->getGroup());
        $paymentAccount = $payment->getPaymentAccount();
        $contract = $payment->getContract();
        $groupSettings = $contract->getGroup()->getGroupSettings();
        $order->setSum($payment->getAmount() + $payment->getOther());
        $order->setUser($paymentAccount->getUser());
        $order->setPaymentProcessor($groupSettings->getPaymentProcessor());
        $order->setPaymentAccount($paymentAccount);
        $order->setDepositAccount($payment->getDepositAccount());
        $order->setDescriptor($contract->getGroup()->getStatementDescriptor());

        $this->createRentOperations($payment, $order);

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($contract->getGroupSettings()->getFeeCC() / 100), 2));
            $order->setPaymentType(OrderPaymentType::CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            if (true === $groupSettings->isPassedAch()) {
                $order->setFee($groupSettings->getFeeACH());
            } else {
                $order->setFee(0);
            }

            $order->setPaymentType(OrderPaymentType::BANK);
        }

        return $order;
    }

    /**
     * Creates a new order for custom payment.
     *
     * @param  Payment $payment
     * @return Order
     */
    public function createCustomOrder(Payment $payment)
    {
        $order = OrderFactory::getOrder($payment->getContract()->getGroup());
        $paymentAccount = $payment->getPaymentAccount();
        $contract = $payment->getContract();
        $groupSettings = $contract->getGroup()->getGroupSettings();
        $order->setSum($payment->getTotal());
        $order->setUser($paymentAccount->getUser());
        $order->setPaymentProcessor($groupSettings->getPaymentProcessor());
        $order->setPaymentAccount($paymentAccount);
        $order->setDepositAccount($payment->getDepositAccount());
        $order->setDescriptor($contract->getGroup()->getStatementDescriptor());

        $this->createCustomOperation($payment, $order);

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($contract->getGroupSettings()->getFeeCC() / 100), 2));
            $order->setPaymentType(OrderPaymentType::CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            if (true === $groupSettings->isPassedAch()) {
                $order->setFee($groupSettings->getFeeACH());
            } else {
                $order->setFee(0);
            }

            $order->setPaymentType(OrderPaymentType::BANK);
        }

        return $order;
    }

    /**
     * Creates a new order for credit track payment.
     *
     * @param  PaymentAccount $paymentAccount
     * @return OrderSubmerchant
     */
    public function createCreditTrackOrder(PaymentAccount $paymentAccount)
    {
        $order = new OrderSubmerchant();
        $order->setUser($paymentAccount->getUser());
        $order->setSum($this->creditTrackAmount);
        /** @var Group $rentTrackGroup */
        $rentTrackGroup = $this->em->getRepository('DataBundle:Group')->findOneByCode($this->rtGroupCode);
        $order->setPaymentProcessor($rentTrackGroup->getGroupSettings()->getPaymentProcessor());
        $order->setPaymentAccount($paymentAccount);
        $order->setDepositAccount($rentTrackGroup->getRentDepositAccountForCurrentPaymentProcessor());

        $this->createReportOperation($order);

        /** @var GroupSettings $groupSettings */
        $groupSettings = $rentTrackGroup->getGroupSettings();

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($groupSettings->getFeeCC() / 100), 2));
            $order->setPaymentType(OrderPaymentType::CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            $order->setFee($groupSettings->getFeeACH());
            $order->setPaymentType(OrderPaymentType::BANK);
        }

        return $order;
    }

    /**
     * @param Group $group
     * @param float $amount
     * @param string $descriptor
     * @return OrderSubmerchant
     */
    public function createChargeOrder(Group $group, $amount, $descriptor)
    {
        $order = new OrderSubmerchant();

        $operation = new Operation();
        $operation->setType(OperationType::CHARGE);
        $operation->setAmount($amount);
        $operation->setGroup($group);
        $operation->setPaidFor(new DateTime());
        $operation->setOrder($order);

        $order->addOperation($operation);

        $users = $group->getGroupAgents();
        if ($users->count() == 0) {
            throw new \RuntimeException(
                sprintf(
                    "Can't create charge order: user for group '%s' not found.",
                    $group->getName()
                )
            );
        }

        $groupUser = $users->first();

        $order->setPaymentType(OrderPaymentType::BANK);
        $order->setUser($groupUser);
        $order->setSum($amount);
        $order->setDescriptor($descriptor);
        $order->setPaymentProcessor($group->getGroupSettings()->getPaymentProcessor());

        /** @var Group $rentTrackGroup */
        $rentTrackGroup = $this->em->getRepository('DataBundle:Group')->findOneByCode($this->rtGroupCode);

        $order->setDepositAccount($rentTrackGroup->getRentDepositAccountForCurrentPaymentProcessor());

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
     * @param Payment $payment
     * @param Order $order
     */
    protected function createCustomOperation(Payment $payment, Order $order)
    {
        $operation = new Operation();
        $operation->setOrder($order);
        $operation->setType(OperationType::CUSTOM);
        $operation->setContract($payment->getContract());
        $operation->setAmount($payment->getTotal());
        $operation->setPaidFor($payment->getPaidFor() ? $payment->getPaidFor() : new \DateTime());
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

    /**
     * Creates a new REPORT type operation for a given order.
     *
     * @param  OrderSubmerchant $order
     * @return Operation
     */
    protected function createReportOperation(OrderSubmerchant $order)
    {
        $operation = new Operation();
        $operation->setPaidFor(new DateTime());
        $operation->setAmount($order->getSum());
        $operation->setType(OperationType::REPORT);
        $operation->setOrder($order);
    }
}
