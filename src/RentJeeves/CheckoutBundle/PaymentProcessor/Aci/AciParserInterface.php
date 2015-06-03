<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci;

use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;

interface AciParserInterface
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
