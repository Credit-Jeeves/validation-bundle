<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\MakePaymentRequest;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("payment.pay_rent")
 */
class PayRent extends Pay
{
    public function executePayment(Payment $payment)
    {
        $paymentAccount = $payment->getPaymentAccount();
        $contract = $payment->getContract();
        $order = $this->getOrder();
        $order->setSum($payment->getAmount() + $payment->getOther());
        $order->setUser($paymentAccount->getUser());
        $order->setStatus(OrderStatus::NEWONE);

        if ($amount = $payment->getAmount()) {
            $operation = new Operation();
            $operation->setOrder($order);
            $operation->setType(OperationType::RENT);
            $operation->setContract($contract);
            $operation->setAmount($amount);
            $operation->setPaidFor($payment->getPaidFor());
            $this->em->persist($operation);
        }
        if ($amount = $payment->getOther()) {
            $operation = new Operation();
            $operation->setOrder($order);
            $operation->setType(OperationType::OTHER);
            $operation->setContract($contract);
            $operation->setAmount($amount);
            $operation->setPaidFor($payment->getPaidFor());
            $this->em->persist($operation);
        }

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($contract->getDepositAccount()->getFeeCC() / 100), 2));
            $order->setType(OrderType::HEARTLAND_CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            $order->setFee($contract->getDepositAccount()->getFeeACH());
            $order->setType(OrderType::HEARTLAND_BANK);
        }

        $paymentDetails = $this->getPaymentDetails();
        $paymentDetails->setMerchantName($contract->getGroup()->getMerchantName());

        /** @var MakePaymentRequest $request */
        $request = $paymentDetails->getRequest();

        /** @var BillTransaction $billTransaction */
        $billTransaction = $request->getBillTransactions()->getBillTransaction()[0];

        $billTransaction->setID1(str_replace(",", "", $contract->getProperty()->getFullAddress()));
        if ($contract->getUnit()) { // For houses, there are no units
            $billTransaction->setID2($contract->getUnit()->getName());
        }
        $tenant = $contract->getTenant();
        $billTransaction->setID3(sprintf("%s %s", $tenant->getFirstName(), $tenant->getLastName()));
        $billTransaction->setID4($contract->getGroup()->getName());

        if (PaymentTypeEnum::ONE_TIME == $payment->getType() ||
            date('n') == $payment->getEndMonth() && date('Y') == $payment->getEndYear()
        ) {
            $payment->setStatus(PaymentStatus::CLOSE);
            $this->em->persist($payment);
        }
        $this->em->persist($order);
        $this->em->flush();

        $this->addToken($payment->getPaymentAccount()->getToken());

        $statusRequest = $this->execute();

        if ($statusRequest->isSuccess()) {
            $order->setStatus(OrderStatus::PENDING);
            $status = $contract->getStatus();
            if (in_array($status, array(ContractStatus::INVITE, ContractStatus::APPROVED))) {
                $contract->setStatus(ContractStatus::CURRENT);
            }
        } else {
            $order->setStatus(OrderStatus::ERROR);
        }
        $paymentDetails->setIsSuccessful($statusRequest->isSuccess());
        $this->em->persist($paymentDetails);
        $this->em->persist($order);
        $this->em->persist($contract);
        $this->em->flush();
        $this->em->clear();

        return $statusRequest;
    }
}
