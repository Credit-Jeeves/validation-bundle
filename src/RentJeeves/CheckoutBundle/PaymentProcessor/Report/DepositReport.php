<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

class DepositReport extends PaymentProcessorReport
{
    /**
     * @param DepositReportTransaction $transaction
     */
    public function addTransaction(DepositReportTransaction $transaction)
    {
        $this->transactions[] = $transaction;
    }
}
