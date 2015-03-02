<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

class ReversalReport
{
    protected $transactions = [];

    /**
     * @return mixed
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param ReversalReportTransaction $transaction
     */
    public function addTransaction(ReversalReportTransaction $transaction)
    {
        $this->transactions[] = $transaction;
    }
}
