<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\TransUnion;

use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionNegativeReport;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Report\RentalReportDataManager;

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
            ->set('c.transUnionStartAt', ':date')
            ->setParameter('date', '2015-01-01')
            ->getQuery()
            ->execute();

        $params = RentalReportDataManager::getRentalReportData(
            $month,
            $startDate,
            $endDate,
            CreditBureau::TRANS_UNION,
            RentalReportType::NEGATIVE
        );
        /** @var TransUnionNegativeReport $report */
        $report = $this->getContainer()->get('rental_report.factory')->getReport($params);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionNegativeReport', $report);
        $report->build($params);

        $this->assertEquals('trans_union_rental', $report->getSerializationType());

        $today = new \DateTime();
        $expectedReportName = sprintf('transunion-negative_renttrack-%s.txt', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $report = $this->getContainer()->get('jms_serializer')->serialize($report, 'trans_union_rental');
        $reportRecords = explode("\n", trim($report));
        $this->assertCount(2, $reportRecords, 'TU report should contain 2 records'); // header + 1 contract
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
