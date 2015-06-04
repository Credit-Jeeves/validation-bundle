<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\Experian;

use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\RentalReportFactory;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ExperianClosureReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldMakeClosureReportForExperian()
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
        $contract->setReportToExperian(true);
        $oneMonthAgo = new \DateTime('-1 month');
        $contract->setExperianStartAt($oneMonthAgo);
        $today = new \DateTime();
        $contract->setFinishAt($today);
        $em->flush($contract);

        $report = RentalReportFactory::getExperianReport(RentalReportType::CLOSURE, $em);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianClosureReport', $report);
        $params = $this->getRentalReportParams($today, $oneMonthAgo, $today);
        $report->build($params);
        $this->assertEquals('csv', $report->getSerializationType());

        $expectedReportName = sprintf('renttrack-closure-%s.csv', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $result = $this->getContainer()->get('jms_serializer')->serialize($report, 'csv');
        $expectedResult = file_get_contents(__DIR__.'/../../../Fixtures/Report/experian_closure.csv');
        $this->assertEquals(trim($expectedResult), trim($result));
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
