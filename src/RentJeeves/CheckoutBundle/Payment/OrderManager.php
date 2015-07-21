<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\Services\PaidFor;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Model\GroupSettings;
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
     * @return OrderSubmerchant
     */
    public function createRentOrder(Payment $payment)
    {
        $order = OrderFactory::getOrder($payment->getContract()->getGroup());
        $paymentAccount = $payment->getPaymentAccount();
        $contract = $payment->getContract();
        $groupSettings = $contract->getGroup()->getGroupSettings();
        $order->setSum($payment->getAmount() + $payment->getOther());
        $order->setUser($paymentAccount->getUser());
        $order->setStatus(OrderStatus::NEWONE);
        $order->setPaymentProcessor($payment->getContract()->getGroup()->getGroupSettings()->getPaymentProcessor());

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
        $order->setStatus(OrderStatus::NEWONE);
        /** Not implement for ACI, PaymentProcess should be gotten from Group */
        $order->setPaymentProcessor(PaymentProcessor::HEARTLAND);

        /** @var GroupSettings $groupSettings */
        $groupSettings = $this->em->getRepository('DataBundle:Group')
            ->findOneByCode($this->rtMerchantName)->getGroupSettings();

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
        $order->setStatus(OrderStatus::NEWONE);
        $order->setDescriptor($descriptor);
        $order->setPaymentProcessor($group->getGroupSettings()->getPaymentProcessor());

        return $order;
    }

    /**
     * Creates rent operations for given payment and order.
     *
     * @param Payment $payment
     * @param OrderSubmerchant   $order
     */
    protected function createRentOperations(Payment $payment, OrderSubmerchant $order)
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
     * @param OrderSubmerchant   $order
     */
    protected function createBalanceBasedOperations(Payment $payment, OrderSubmerchant $order)
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
     * @param OrderSubmerchant   $order
     */
    protected function createRegularOperations(Payment $payment, OrderSubmerchant $order)
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
