<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Monolog\Logger;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\MakePaymentRequest;
use RentJeeves\CheckoutBundle\Services\PaidFor;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use JMS\DiExtraBundle\Annotation as DI;
use RuntimeException;
use RentJeeves\CoreBundle\DateTime;

/**
 * @DI\Service("payment.pay_rent")
 */
class PayRent extends Pay
{
    /**
     * @var PaidFor
     */
    protected $paidFor;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @DI\InjectParams({"paidFor" = @DI\Inject("checkout.paid_for")})
     *
     * @param PaidFor $paidFor
     *
     * @return $this
     */
    public function setPaidFor(PaidFor $paidFor)
    {
        $this->paidFor = $paidFor;
        return $this;
    }

    /**
     * @DI\InjectParams({"logger" = @DI\Inject("logger")})
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function executePayment(Payment $payment)
    {
        $this->logger->debug('Get new order for payment ID %s' . $payment->getId());
        $order = $this->getOrder();
        $paymentAccount = $payment->getPaymentAccount();
        $contract = $payment->getContract();
        $order->setSum($payment->getAmount() + $payment->getOther());
        $order->setUser($paymentAccount->getUser());
        $order->setStatus(OrderStatus::NEWONE);

        $this->createOperations($payment, $order);

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($contract->getDepositAccount()->getFeeCC() / 100), 2));
            $order->setType(OrderType::HEARTLAND_CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            $order->setFee($contract->getDepositAccount()->getFeeACH());
            $order->setType(OrderType::HEARTLAND_BANK);
        }

        $paymentDetails = $this->getPaymentDetails();
        $paymentDetails->setPaymentAccount($paymentAccount);
        $paymentDetails->setMerchantName($contract->getGroup()->getMerchantName());

        /** @var MakePaymentRequest $request */
        $request = $paymentDetails->getRequest();

        /** @var BillTransaction $billTransaction */
        $billTransaction = $request->getBillTransactions()->getBillTransaction()[0];

        $billTransaction->setID1(str_replace(",", "", $contract->getProperty()->getShrinkAddress()));
        if ($contract->getUnit()) { // For houses, there are no units
            $billTransaction->setID2($contract->getUnit()->getName());
        }
        $tenant = $contract->getTenant();
        $billTransaction->setID3(sprintf("%s %s", $tenant->getFirstName(), $tenant->getLastName()));
        $billTransaction->setID4($contract->getGroup()->getID4StatementDescriptor());

        if (PaymentTypeEnum::ONE_TIME == $payment->getType() ||
            date('n') == $payment->getEndMonth() && date('Y') == $payment->getEndYear()
        ) {
            $payment->setClosed($this, PaymentCloseReason::EXECUTED);
        }
        $this->em->persist($order);
        $this->em->flush();

        $this->addToken($payment->getPaymentAccount()->getToken());

        $statusRequest = $this->execute();

        if ($statusRequest->isSuccess()) {
            $order->setStatus($this->getSuccessfulOrderStatus($order));
            $status = $contract->getStatus();
            if (in_array($status, array(ContractStatus::INVITE, ContractStatus::APPROVED))) {
                $contract->setStatus(ContractStatus::CURRENT);
            }
        } else {
            $order->setStatus(OrderStatus::ERROR);
            if (OrderType::HEARTLAND_CARD == $order->getType() && $payment->isRecurring()) {
                $this->logger->debug(
                    'Close CC recurring payment ID ' . $payment->getId() . ' for order ID ' . $order->getId()
                );
                $payment->setClosed($this, PaymentCloseReason::RECURRING_ERROR);
            }
        }
        $this->logger->debug('New order ID ' . $order->getId() . ', status: ' . $order->getStatus());
        $paymentDetails->setIsSuccessful($statusRequest->isSuccess());
        $this->em->persist($paymentDetails);
        $this->em->persist($contract);
        $this->em->flush();
        $this->em->clear();

        return $statusRequest;
    }

    protected function createOperations(Payment $payment, Order $order)
    {
        $contract = $payment->getContract();
        $payBalanceOnly = $contract->getGroup()->getGroupSettings()->getPayBalanceOnly();

        if ($payBalanceOnly) {
            $this->createBalanceBasedOperations($payment, $order);
        } else {
            $this->createRegularOperations($payment, $order);
        }
    }

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

    protected function getSuccessfulOrderStatus(Order $order)
    {
        if (OrderType::HEARTLAND_CARD == $order->getType()) {
            return OrderStatus::COMPLETE;
        }

        return OrderStatus::PENDING;
    }
}
