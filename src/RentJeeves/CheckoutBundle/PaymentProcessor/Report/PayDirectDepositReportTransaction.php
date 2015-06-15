<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

class PayDirectDepositReportTransaction extends PaymentProcessorReportTransaction
{
    /**
     * @var \DateTime
     */
    protected $depositDate;

    /**
     * @return \DateTime
     */
    public function getDepositDate()
    {
        return $this->depositDate;
    }

    /**
     * @param \DateTime $depositDate
     */
    public function setDepositDate($depositDate)
    {
        $this->depositDate = $depositDate;
    }
}
