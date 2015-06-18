<?php

namespace RentJeeves\TestBundle\Aci;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\ReportLoader as Base;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction;

class CollectPayReportLoaderTest extends Base
{
    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        $responseFromLoader = new PaymentProcessorReport();
        $responseFromLoader->addTransaction(new ReversalReportTransaction());
        $responseFromLoader->addTransaction(new ReversalReportTransaction());

        return $responseFromLoader;
    }
}
