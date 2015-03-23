<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use RentJeeves\CoreBundle\DateTime;

class ReversalReportTransaction extends PaymentProcessorReportTransaction
{
    const TYPE_CANCEL = 'cancel';
    const TYPE_RETURN = 'return';
    const TYPE_REFUND = 'refund';
    const TYPE_COMPLETE = 'complete';

    /** @var DateTime */
    protected $transactionDate;

    /** @var string */
    protected $transactionType;

    /** @var float */
    protected $amount;

    /** @var string */
    protected $originalTransactionId;

    /** @var string */
    protected $batchId;

    /** @var string */
    protected $reversalDescription;

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
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
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalTransactionId()
    {
        return $this->originalTransactionId;
    }

    /**
     * @param string $originalTransactionId
     */
    public function setOriginalTransactionId($originalTransactionId)
    {
        $this->originalTransactionId = $originalTransactionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getReversalDescription()
    {
        return $this->reversalDescription;
    }

    /**
     * @param string $reversalDescription
     */
    public function setReversalDescription($reversalDescription)
    {
        $this->reversalDescription = $reversalDescription;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * @param DateTime $transactionDate
     */
    public function setTransactionDate($transactionDate)
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * @param string $transactionType
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;

        return $this;
    }
}
