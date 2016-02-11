<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\Equifax;

use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\Equifax\EquifaxPositiveReport;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Report\RentalReportDataManager;

class EquifaxPositiveReportCase extends BaseTestCase
{
    /**
     * @test
     * @dataProvider provideReport
     */
    public function shouldMakePositiveReportForEquifax(
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
            CreditBureau::EQUIFAX,
            RentalReportType::POSITIVE
        );
        /** @var EquifaxPositiveReport $report */
        $report = $this->getContainer()->get('rental_report.factory')->getReport($params);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Equifax\EquifaxPositiveReport', $report);
        $report->build($params);

        $this->assertEquals('rental_1', $report->getSerializationType());

        $today = new \DateTime();
        $expectedReportName = sprintf('equifax-positive_renttrack-%s.txt', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        // check only record, b/c header doesn't contain important info and has changeable data
        $report = $this->getContainer()->get('jms_serializer')->serialize($report, 'rental_1');
        $reportRecords = explode("\n", trim($report));
        $this->assertCount(2, $reportRecords, 'Equifax report should contain 2 records');
        $reportRecord = $reportRecords[1];
        $expectedResult = trim(file_get_contents($expectedResultFilename));

        $this->assertEquals($expectedResult, $reportRecord);
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
                __DIR__.'/../../../Fixtures/Report/equifax.txt'
            ],
        ];
    }

    /**
     * @param \DateTime $month
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return RentalReportData
     */
    protected function getRentalReportParams(\DateTime $month, \DateTime $startDate, \DateTime $endDate)
    {
        $params = new RentalReportData();
        $params->setMonth($month);
        $params->setStartDate($startDate);
        $params->setEndDate($endDate);

        return $params;
    }
}
