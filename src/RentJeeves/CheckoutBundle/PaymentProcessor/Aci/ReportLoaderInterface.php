<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci;

use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;

interface ReportLoaderInterface
{
    /**
     * Return  processed data from external services
     *
     * @return PaymentProcessorReport
     */
    public function loadReport();
}
