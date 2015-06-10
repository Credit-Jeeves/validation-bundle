<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

class PayDirectReversalReportTransaction extends PaymentProcessorReportTransaction
{
    const TYPE_RETURN = 'return';
    const TYPE_REFUND = 'refund';

    /**
     * @var \DateTime
     */
    protected $transactionDate;

    /**
     * @var string
     */
    protected $transactionType;

    /**
     * @var string
     */
    protected $reversalDescription;

    /**
     * @return \DateTime
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * @param \DateTime $transactionDate
     */
    public function setTransactionDate($transactionDate)
    {
        $this->transactionDate = $transactionDate;
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
    }
}
