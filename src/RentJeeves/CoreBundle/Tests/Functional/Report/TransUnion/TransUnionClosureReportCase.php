<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\TransUnion;

use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\RentalReportFactory;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class TransUnionClosureReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldMakeClosureReportForTransUnion()
    {
        $this->load(true);
        $em = $this->getEntityManager();

        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            [
                'status' => ContractStatus::FINISHED,
                'rent' => 1500,
                'dueDate' => 4,
            ]
        );
        $this->assertNotNull($contract, 'No finished contracts found');
        $contract->setReportToTransUnion(true);
        $oneMonthAgo = new \DateTime('-1 month');
        $contract->setTransUnionStartAt($oneMonthAgo);
        $today = new \DateTime();
        $contract->setFinishAt($today);
        $em->flush($contract);

        $report = RentalReportFactory::getTransUnionReport(RentalReportType::CLOSURE, $em, []);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionClosureReport', $report);
        $params = $this->getRentalReportParams($today, $oneMonthAgo, $today);
        $report->build($params);

        $this->assertEquals('trans_union_rental', $report->getSerializationType());

        $expectedReportName = sprintf('renttrack-closure-%s.txt', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $report = $this->getContainer()->get('jms_serializer')->serialize($report, 'trans_union_rental');
        $reportRecords = explode("\n", trim($report));
        $this->assertCount(2, $reportRecords, 'TU report should contain 2 records: header and 1 finished contract');
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
