<?php

namespace RentJeeves\CoreBundle\Tests\Report;

use RentJeeves\CoreBundle\Report\TransUnionRentalReport;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use DateTime;

class TransUnionRentalReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldMakeReportForTransUnion()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $month = '2';
        $year = '2014';
        $report = new TransUnionRentalReport($em, $month, $year);

        $this->assertEquals('trans_union_rental', $report->getSerializationType());

        $today = new DateTime();
        $expectedReportName = sprintf('renttrack-%s.txt', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        // check only record, b/c header doesn't contain important info and has changeable data
        $report = $this->getContainer()->get('jms_serializer')->serialize($report, 'trans_union_rental');
        $reportRecord = trim(explode("\n", $report)[1]);
        $expectedResult = trim(file_get_contents(__DIR__.'/../Fixtures/Report/transunion.txt'));

        $this->assertEquals($expectedResult, $reportRecord);
    }
}
