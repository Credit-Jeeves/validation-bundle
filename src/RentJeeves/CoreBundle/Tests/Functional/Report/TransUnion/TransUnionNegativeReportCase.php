<?php

namespace RentJeeves\CoreBundle\Tests\Report;

use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\RentalReportFactory;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class TransUnionNegativeReportCase extends BaseTestCase
{
    /**
     * @test
     * @dataProvider provideReport
     */
    public function shouldMakeNegativeReportForTransUnion(
        \DateTime $month,
        \DateTime $startDate,
        \DateTime $endDate
    ) {
        $this->load(true);
        $em = $this->getEntityManager();

        $em->getRepository('RjDataBundle:Contract')
            ->createQueryBuilder('c')
            ->update()
            ->set('c.reportToTransUnion', 1)
            ->set('c.transUnionStartAt', '2015-01-01')
            ->getQuery()
            ->execute();

        $report = RentalReportFactory::getTransUnionReport(RentalReportType::NEGATIVE, $em, []);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionNegativeReport', $report);
        $params = $this->getRentalReportParams($month, $startDate, $endDate);
        $report->build($params);

        $this->assertEquals('trans_union_rental', $report->getSerializationType());

        $today = new \DateTime();
        $expectedReportName = sprintf('renttrack-negative-%s.txt', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $report = $this->getContainer()->get('jms_serializer')->serialize($report, 'trans_union_rental');
        $reportRecords = explode("\n", trim($report));
        $this->assertCount(3, $reportRecords, 'TU report should contain 3 records'); // header + 2 contracts
    }

    /**
     * @return array
     */
    public function provideReport()
    {
        return [
            [
                new \DateTime(),
                new \DateTime('-1 month'),
                new \DateTime(),
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
