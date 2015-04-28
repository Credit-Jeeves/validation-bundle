<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use Payum2\Heartland\Model\PaymentDetails;
use RentJeeves\DataBundle\Entity\Transaction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("payment.heartland.payment_details_mapper", public=false)
 */
class PaymentDetailsMapper
{
    /**
     * @param  PaymentDetails $paymentDetails
     * @param  Transaction    $transaction
     * @return Transaction
     */
    public function map(PaymentDetails $paymentDetails, Transaction $transaction = null)
    {
        $transaction = $transaction ?: new Transaction();

        $transaction->setMerchantName($paymentDetails->getMerchantName());
        $transaction->setAmount($paymentDetails->getAmount());
        $transaction->setBatchId($paymentDetails->getBatchId());
        $transaction->setTransactionId($paymentDetails->getTransactionId());
        $transaction->setIsSuccessful($paymentDetails->getIsSuccessful());
        $transaction->setMessages($paymentDetails->getMessages());

        return $transaction;
    }
}
