<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;


abstract class PaymentProcessorReport
{
    protected $transactions = [];

    /**
     * @return array
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}
