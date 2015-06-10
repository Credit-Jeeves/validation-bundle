<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\Experian;

use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\Experian\ExperianPositiveReport;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Report\RentalReportDataManager;

class ExperianPositiveReportCase extends BaseTestCase
{
    /**
     * @test
     * @dataProvider provideReport
     */
    public function shouldMakePositiveReportForExperian(
        \DateTime $month,
        \DateTime $startDate,
        \DateTime $endDate,
        $expectedResultFilename
    ) {
        $this->load(true);

        $params = RentalReportDataManager::getRentalReportData(
            $month,
            $startDate,
            $endDate,
            CreditBureau::EXPERIAN,
            RentalReportType::POSITIVE
        );
        /** @var ExperianPositiveReport $report */
        $report = $this->getContainer()->get('rental_report.factory')->getReport($params);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianPositiveReport', $report);
        $report->build($params);
        $this->assertEquals('csv', $report->getSerializationType());

        $today = new \DateTime();
        $expectedReportName = sprintf('experian-positive_renttrack-raw-%s.csv', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $result = $this->getContainer()->get('jms_serializer')->serialize($report, 'csv');
        $expectedResult = file_get_contents($expectedResultFilename);
        $this->assertEquals(trim($expectedResult), trim($result));
    }

    /**
     * @return array
     */
    public function provideReport()
    {
        return [
            [
                new \DateTime('2014/02/01'),
                new \DateTime('2014/02/01'),
                new \DateTime('2014/02/28'),
                __DIR__.'/../../../Fixtures/Report/experian.csv'
            ],
            // Multiple case transaction below also covers late case
            [
                new \DateTime('2014/04/01'),
                new \DateTime('2014/04/01'),
                new \DateTime('2014/05/10'),
                __DIR__.'/../../../Fixtures/Report/experian_multiple_transactions.csv'
            ],
        ];
    }
}
