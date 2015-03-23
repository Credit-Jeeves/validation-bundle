<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;


abstract class PaymentProcessorReportTransaction
{
    /** @var string */
    protected $transactionId;

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }
}
