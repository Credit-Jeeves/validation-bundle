<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\Experian;

use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\RentalReportFactory;
use RentJeeves\TestBundle\Functional\BaseTestCase;

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
        $em = $this->getEntityManager();

        $report = RentalReportFactory::getExperianReport(RentalReportType::POSITIVE, $em);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianPositiveReport', $report);
        $params = $this->getRentalReportParams($month, $startDate, $endDate);
        $report->build($params);
        $this->assertEquals('csv', $report->getSerializationType());

        $today = new \DateTime();
        $expectedReportName = sprintf('renttrack-positive-%s.csv', $today->format('Ymd'));

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
