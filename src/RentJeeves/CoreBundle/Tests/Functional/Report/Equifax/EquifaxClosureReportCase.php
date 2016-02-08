<?php

namespace RentJeeves\CoreBundle\Tests\Functional\Report\Equifax;

use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\Equifax\EquifaxClosureReport;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Report\RentalReportDataManager;

class EquifaxClosureReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldMakeClosureReportForEquifax()
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
        $contract->setReportToEquifax(true);
        $oneMonthAgo = new \DateTime('-1 month');
        $contract->setEquifaxStartAt($oneMonthAgo);
        $today = new \DateTime();
        $contract->setFinishAt($today);
        $em->flush($contract);

        $params = RentalReportDataManager::getRentalReportData(
            $today,
            $oneMonthAgo,
            $today,
            CreditBureau::EQUIFAX,
            RentalReportType::CLOSURE
        );
        /** @var EquifaxClosureReport $report */
        $report = $this->getContainer()->get('rental_report.factory')->getReport($params);
        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Equifax\EquifaxClosureReport', $report);
        $report->build($params);

        $this->assertEquals('rental_1', $report->getSerializationType());

        $expectedReportName = sprintf('equifax-closure_renttrack-%s.txt', $today->format('Ymd'));

        $this->assertEquals($expectedReportName, $report->getReportFilename());

        $report = $this->getContainer()->get('jms_serializer')->serialize($report, 'rental_1');
        $reportRecords = explode("\n", trim($report));
        $this->assertCount(2, $reportRecords, 'Equifax report needs 2 records: header and 1 finished contract');
    }
}
