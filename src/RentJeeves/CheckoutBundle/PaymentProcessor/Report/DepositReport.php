<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

class DepositReport
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
     * @param DepositReportTransaction $transaction
     */
    public function addTransaction(DepositReportTransaction $transaction)
    {
        $this->transactions[] = $transaction;
    }
}
