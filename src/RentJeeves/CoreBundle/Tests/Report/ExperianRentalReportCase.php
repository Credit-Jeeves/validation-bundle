<?php

namespace RentJeeves\CoreBundle\Tests\Report;

use RentJeeves\CoreBundle\Report\ExperianRentalReport;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use DateTime;

class ExperianRentalReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldMakeReportForExperian()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $month = '2';
        $year = '2014';
        $report = new ExperianRentalReport($em, $month, $year);

        $this->assertEquals('csv', $report->getSerializationType());

        $today = new DateTime();
        $expectedReportName = sprintf('renttrack-full-%s.csv', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $result = $this->getContainer()->get('jms_serializer')->serialize($report, 'csv');
        $expectedResult = file_get_contents(__DIR__.'/../Fixtures/Report/experian.csv');
        $this->assertEquals(trim($expectedResult), trim($result));
    }
}
