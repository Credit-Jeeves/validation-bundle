<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC;

use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfInt;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\GetCreditandDebitReportsResponse;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\TransactionReportingClient;

/**
 * Service name "payment_processor.profit_stars.rdc.report_loader"
 */
class ReportLoader
{
    /** @var TransactionReportingClient */
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Here is a skeleton to make sure that TransactionReportingClient works.
     * It does not do a real job.
     *
     * @return PaymentProcessorReport
     */
    public function loadReport()
    {
        $locations = new ArrayOfInt();
        $locations->setInt([1023318, 1023322]);

        $beginDate = '2015-11-01';
        $endDate = '2015-12-07';

        /** @var GetCreditandDebitReportsResponse $report */
        $report = $this->client->GetCreditandDebitReports(
            765350,
            'nAfv+O9D5V3i1Pgd3yaUxOAa2D9z',
            223586,
            $locations,
            $beginDate,
            $endDate
        );
        $data = $report->getGetCreditandDebitReportsResult()->getWSCreditDebitReport();

        return new PaymentProcessorReport();
    }
}
