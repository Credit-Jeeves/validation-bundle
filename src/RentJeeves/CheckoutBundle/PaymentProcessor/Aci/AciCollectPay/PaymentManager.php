<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Order;
use Payum\AciCollectPay\Model\Enum\FundingAccountType;
use Payum\AciCollectPay\Model\Payment;
use Payum\AciCollectPay\Request\CaptureRequest\Capture;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\PaymentAccountType;

/**
 * @DI\Service("payment.aci_collect_pay.payment_manager", public=false)
 */
class PaymentManager extends AbstractManager
{
    /**
     * @param  Order          $order
     * @param  PaymentAccount $paymentAccount
     * @return string
     */
    public function executePayment(Order $order, PaymentAccount $paymentAccount)
    {
        $payment = new Payment();

        $payment->setProfileId($order->getUser()->getAciCollectPayProfileId());
        $payment->setFundingAccountId($paymentAccount->getToken());
        $payment->setTransactionCode($order->getId());
        $payment->setDivisionBusinessId($order->getContract()->getGroup()->getAciCollectPaySettings()->getBusinessId());
        $payment->setBillingAccountNumber($order->getContract()->getId());
        $payment->setAmount($order->getSum());
        $payment->setFee($order->getFee());

        if ($paymentAccount->getType() == PaymentAccountType::BANK) {
            $payment->setFundingAccountType(FundingAccountType::BANK);
        } else {
            $payment->setFundingAccountType(FundingAccountType::CCARD);
        }

        $request = new Capture($payment);

        $transaction = new Transaction();

        $transaction->setOrder($order);
        $transaction->setMerchantName($order->getContract()->getGroup()->getAciCollectPaySettings()->getBusinessId());
        $transaction->setPaymentAccount($paymentAccount);
        $transaction->setAmount($order->getSum() + $order->getFee());

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI CollectPay Critical Error]:%s', $e->getMessage()));
            $transaction->setMessages($e->getMessage());
            $transaction->setIsSuccessful(false);
            $this->em->persist($transaction);
            $this->em->flush();
            throw new $e();
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(sprintf('[ACI CollectPay Error]:%s', $request->getMessages()));
        }

        $transaction->setMessages($request->getMessages());
        $transaction->setIsSuccessful($request->getIsSuccessful());
        $transaction->setTransactionId($request->getModel()->getConfirmationNumber());

        $order->addTransaction($transaction);

        $this->em->persist($transaction);

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Created new %s transaction for contract with id = "%d"',
                $request->getIsSuccessful() ? "successful" : "failed",
                $order->getContract()->getId()
            )
        );

        return $this->getOrderStatus($request->getIsSuccessful());
    }

    /**
     * @param  bool   $isSuccessful
     * @return string
     */
    protected function getOrderStatus($isSuccessful)
    {
        if (!$isSuccessful) {
            return OrderStatus::ERROR;
        }

        return OrderStatus::COMPLETE;
    }
}
