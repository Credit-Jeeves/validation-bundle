<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;


class PaymentProcessorReport
{
    /** @var array */
    protected $transactions = [];

    /**
     * @return array
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param array<PaymentProcessorReportTransaction> $transactions
     */
    public function setTransactions(array $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * @param PaymentProcessorReportTransaction $transaction
     */
    public function addTransaction(PaymentProcessorReportTransaction $transaction)
    {
        $this->transactions[] = $transaction;
    }
}
