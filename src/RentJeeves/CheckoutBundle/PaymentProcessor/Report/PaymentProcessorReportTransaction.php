<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

abstract class PaymentProcessorReportTransaction
{
    /** @var string */
    protected $transactionId;

    /** @var string */
    protected $batchId;

    /** @var float */
    protected $amount;

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     *
     * @return self
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @param string $batchId
     *
     * @return self
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }
}
