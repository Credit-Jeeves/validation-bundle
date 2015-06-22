<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\Report;

use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;

interface CollectPayParserInterface
{
    /**
     * Parses given array Transactions.
     *
     * @param mixed $data
     *
     * @return PaymentProcessorReportTransaction[]
     */
    public function parse($data);
}
