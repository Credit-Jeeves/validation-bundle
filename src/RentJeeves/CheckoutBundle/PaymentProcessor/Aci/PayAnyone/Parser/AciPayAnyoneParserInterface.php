<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser;

use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;

interface AciPayAnyoneParserInterface
{
    /**
     * Parses given xml Transactions.
     *
     * @param mixed $xmlData
     *
     * @return PaymentProcessorReportTransaction[]
     */
    public function parse($xmlData);
}
