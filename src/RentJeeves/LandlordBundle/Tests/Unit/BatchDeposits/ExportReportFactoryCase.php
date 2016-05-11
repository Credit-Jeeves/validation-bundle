<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\BatchDeposits;

use CreditJeeves\DataBundle\Entity\Holding;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\LandlordBundle\BatchDeposits\ExportReport\ExportReportFactory;
use RentJeeves\LandlordBundle\BatchDeposits\ExportReport\MRIBostonPostExportReport;
use RentJeeves\LandlordBundle\BatchDeposits\ExportReport\PromasExportReport;
use RentJeeves\LandlordBundle\BatchDeposits\ExportReport\YardiGenesisExportReport;
use RentJeeves\LandlordBundle\BatchDeposits\ExportReport\YardiGenesisV2ExportReport;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ExportReportFactoryCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnNullIfSetUnsupportedExportReport()
    {
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::NONE);
        $factory = new ExportReportFactory(
            $this->getMock(LoggerInterface::class),
            [
                AccountingSystem::PROMAS => $this->getExportReportMock(PromasExportReport::class),
                AccountingSystem::MRI_BOSTONPOST => $this->getExportReportMock(MRIBostonPostExportReport::class),
                AccountingSystem::YARDI_GENESIS => $this->getExportReportMock(YardiGenesisExportReport::class),
                AccountingSystem::YARDI_GENESIS_2 => $this->getExportReportMock(YardiGenesisV2ExportReport::class),
            ]
        );

        $this->assertNull($factory->getExportReport($holding), 'Expected result is null');
    }

    /**
     * @return array
     */
    public function provideSupportedAccountingSystem()
    {
        return [
            [AccountingSystem::PROMAS, PromasExportReport::class],
            [AccountingSystem::MRI_BOSTONPOST, MRIBostonPostExportReport::class],
            [AccountingSystem::YARDI_GENESIS, YardiGenesisExportReport::class],
            [AccountingSystem::YARDI_GENESIS_2, YardiGenesisV2ExportReport::class],
        ];
    }

    /**
     * @test
     * @dataProvider provideSupportedAccountingSystem
     */
    public function shouldReturnExportReport($accountingSystem, $expectedExportReport)
    {
        $holding = new Holding();
        $holding->setAccountingSystem($accountingSystem);

        $factory = new ExportReportFactory(
            $this->getMock(LoggerInterface::class),
            [
                AccountingSystem::PROMAS => $this->getExportReportMock(PromasExportReport::class),
                AccountingSystem::MRI_BOSTONPOST => $this->getExportReportMock(MRIBostonPostExportReport::class),
                AccountingSystem::YARDI_GENESIS => $this->getExportReportMock(YardiGenesisExportReport::class),
                AccountingSystem::YARDI_GENESIS_2 => $this->getExportReportMock(YardiGenesisV2ExportReport::class),
            ]
        );

        $report = $factory->getExportReport($holding);
        $this->assertInstanceOf($expectedExportReport, $report, 'Incorrect instance of Export Report.');
    }

    /**
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getExportReportMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
