<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use RentJeeves\CoreBundle\DateTime;

class ReversalReportTransaction
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
    protected $transactionID;
    /** @var string */
    protected $originalTransactionID;
    /** @var string */
    protected $batchID;
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
    public function getBatchID()
    {
        return $this->batchID;
    }

    /**
     * @param string $batchID
     */
    public function setBatchID($batchID)
    {
        $this->batchID = $batchID;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalTransactionID()
    {
        return $this->originalTransactionID;
    }

    /**
     * @param string $originalTransactionID
     */
    public function setOriginalTransactionID($originalTransactionID)
    {
        $this->originalTransactionID = $originalTransactionID;

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
    public function getTransactionID()
    {
        return $this->transactionID;
    }

    /**
     * @param string $transactionID
     */
    public function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;

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
