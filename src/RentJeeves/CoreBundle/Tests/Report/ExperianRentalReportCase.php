<?php

namespace RentJeeves\CoreBundle\Tests\Report;

use RentJeeves\CoreBundle\Report\ExperianRentalReport;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use DateTime;

class ExperianRentalReportCase extends BaseTestCase
{
    /**
     * @test
     * @dataProvider provide
     */
    public function shouldMakeReportForExperian($month, $year, $expectedResultFilename)
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $report = new ExperianRentalReport($em, $month, $year);

        $this->assertEquals('csv', $report->getSerializationType());

        $today = new DateTime();
        $expectedReportName = sprintf('renttrack-raw-%s.csv', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $result = $this->getContainer()->get('jms_serializer')->serialize($report, 'csv');
        $expectedResult = file_get_contents($expectedResultFilename);
        $this->assertEquals(trim($expectedResult), trim($result));
    }

    public function provide()
    {
        return array(
            array(2, 2014, __DIR__.'/../Fixtures/Report/experian.csv'),
            // Multiple case transaction below also covers late case
            array(4, 2014, __DIR__.'/../Fixtures/Report/experian_multiple_transactions.csv'),
        );
    }
}
