<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\Experian;

use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\Experian\ExperianClosureReport;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Report\RentalReportDataManager;

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
            ]
        );
        $this->assertNotNull($contract, 'No finished contracts found');
        $contract->setReportToExperian(true);
        $oneMonthAgo = new \DateTime('-1 month');
        $contract->setExperianStartAt($oneMonthAgo);
        $today = new \DateTime();
        $contract->setFinishAt($today);
        $em->flush($contract);

        $params = RentalReportDataManager::getRentalReportData(
            $today,
            $oneMonthAgo,
            $today,
            CreditBureau::EXPERIAN,
            RentalReportType::CLOSURE
        );
        /** @var ExperianClosureReport $report */
        $report = $this->getContainer()->get('rental_report.factory')->getReport($params);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianClosureReport', $report);

        $report->build($params);
        $this->assertEquals('csv', $report->getSerializationType());

        $expectedReportName = sprintf('renttrack-closure-%s.csv', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $result = $this->getContainer()->get('jms_serializer')->serialize($report, 'csv');
        $reportRecords = explode("\n", trim($result));
        $this->assertCount(
            2,
            $reportRecords,
            'Experian closure report should contain 2 records: header and one finished contract data'
        );
    }
}
