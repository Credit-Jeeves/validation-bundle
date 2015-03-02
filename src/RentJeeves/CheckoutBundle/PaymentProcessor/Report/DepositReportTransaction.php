<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use RentJeeves\CoreBundle\DateTime;

class DepositReportTransaction
{
    /** @var string */
    protected $batchID;

    /** @var DateTime */
    protected $batchCloseDate;

    /** @var string */
    protected $transactionID;

    /** @var float */
    protected $depositAmount;

    /** @var DateTime */
    protected $depositDate;

    /**
     * @return DateTime
     */
    public function getBatchCloseDate()
    {
        return $this->batchCloseDate;
    }

    /**
     * @param DateTime $batchCloseDate
     */
    public function setBatchCloseDate($batchCloseDate)
    {
        $this->batchCloseDate = $batchCloseDate;

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
     * @return float
     */
    public function getDepositAmount()
    {
        return $this->depositAmount;
    }

    /**
     * @param float $depositAmount
     */
    public function setDepositAmount($depositAmount)
    {
        $this->depositAmount = $depositAmount;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDepositDate()
    {
        return $this->depositDate;
    }

    /**
     * @param DateTime $depositDate
     */
    public function setDepositDate($depositDate)
    {
        $this->depositDate = $depositDate;

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
}
