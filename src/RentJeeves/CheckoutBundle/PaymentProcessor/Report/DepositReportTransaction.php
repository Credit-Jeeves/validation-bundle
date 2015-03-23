<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use RentJeeves\CoreBundle\DateTime;

class DepositReportTransaction extends PaymentProcessorReportTransaction
{
    /** @var string */
    protected $batchId;

    /** @var DateTime */
    protected $batchCloseDate;

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
}
