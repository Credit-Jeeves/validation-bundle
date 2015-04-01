<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use RentJeeves\CoreBundle\DateTime;

class DepositReportTransaction extends PaymentProcessorReportTransaction
{
    /** @var DateTime */
    protected $batchCloseDate;

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
