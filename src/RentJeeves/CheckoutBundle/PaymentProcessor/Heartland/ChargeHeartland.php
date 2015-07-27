<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use Payum2\Heartland\Soap\Base\BillTransaction;
use Payum2\Heartland\Soap\Base\CardProcessingMethod;
use Payum2\Heartland\Soap\Base\MakePaymentRequest;
use Payum2\Heartland\Soap\Base\TokenToCharge;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Heartland\Soap\Base\Transaction as RequestTransaction;

class ChargeHeartland extends BasePayHeartland
{
    /**
     * {@inheritdoc}
     */
    protected function getPaymentDetails(OrderSubmerchant $order, $paymentType)
    {
        /** @var Operation $operation */
        if ((!$operation = $order->getOperations()->first()) || !($group = $operation->getGroup())) {
            throw new PaymentProcessorInvalidArgumentException('Order is invalid');
        }

        $paymentRequest = new MakePaymentRequest();

        $billTransaction = new BillTransaction();
        $billTransaction->setID1(substr($group->getName(), 0, 50));
        $billTransaction->setID4($order->getDescriptor());
        $billTransaction->setBillType('Subscription Services');
        $billTransaction->setAmountToApplyToBill($order->getSum());
        $paymentRequest->getBillTransactions()->setBillTransaction([$billTransaction]);

        $requestTransaction = new RequestTransaction();
        $requestTransaction->setAmount($order->getSum());
        $requestTransaction->setFeeAmount(0);
        $paymentRequest->setTransaction($requestTransaction);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($this->rtMerchantName);
        $paymentDetails->setRequest($paymentRequest);

        return $paymentDetails;
    }

    /**
     * {@inheritdoc}
     */
    protected function addToken(PaymentDetails $paymentDetails, $token, OrderSubmerchant $order)
    {
        $tokenToCharge = new TokenToCharge();
        $tokenToCharge->setAmount($order->getSum());
        $tokenToCharge->setExpectedFeeAmount(0);
        $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
        $tokenToCharge->setToken($token);

        $paymentDetails->getRequest()->getTokensToCharge()->setTokenToCharge($tokenToCharge);
    }
}
