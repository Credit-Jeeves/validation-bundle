<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Report;

use Doctrine\ORM\EntityManagerInterface;
use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\RentalReportFactory;

class RentalReportFactoryCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateReportObjectAsInstanceOfRentalReport()
    {
        $rentalReportData = $this->getRentalReportDataMock();
        $rentalReportData
            ->expects($this->once())
            ->method('getBureau')
            ->will($this->returnValue(CreditBureau::EXPERIAN));
        $rentalReportData
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(RentalReportType::POSITIVE));

        $report = RentalReportFactory::getReport($rentalReportData, $this->getEntityManagerMock(), []);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\RentalReport', $report);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Given report bureau 'Unknown bureau' does not exist
     */
    public function shouldThrowExceptionIfUnknownBureauSpecified()
    {
        $rentalReportData = $this->getRentalReportDataMock();
        $rentalReportData
            ->expects($this->exactly(2))
            ->method('getBureau')
            ->will($this->returnValue('Unknown bureau'));

        RentalReportFactory::getReport($rentalReportData, $this->getEntityManagerMock(), []);
    }

    /**
     * @test
     */
    public function shouldGetTransUnionPositiveReport()
    {
        $report = RentalReportFactory::getTransUnionReport(
            RentalReportType::POSITIVE,
            $this->getEntityManagerMock(),
            []
        );

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionPositiveReport', $report);
    }

    /**
     * @test
     */
    public function shouldGetTransUnionNegativeReport()
    {
        $report = RentalReportFactory::getTransUnionReport(
            RentalReportType::NEGATIVE,
            $this->getEntityManagerMock(),
            []
        );

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionNegativeReport', $report);
    }

    /**
     * @test
     */
    public function shouldGetTransUnionClosureReport()
    {
        $report = RentalReportFactory::getTransUnionReport(
            RentalReportType::CLOSURE,
            $this->getEntityManagerMock(),
            []
        );

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionClosureReport', $report);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage TransUnion report type 'Unknown TransUnion type' does not exist
     */
    public function shouldThrowExceptionIfUnknownTransUnionReportTypeSpecified()
    {
        RentalReportFactory::getTransUnionReport('Unknown TransUnion type', $this->getEntityManagerMock(), []);
    }

    /**
     * @test
     */
    public function shouldGetExperianPositiveReport()
    {
        $report = RentalReportFactory::getExperianReport(RentalReportType::POSITIVE, $this->getEntityManagerMock());

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianPositiveReport', $report);
    }

    /**
     * @test
     */
    public function shouldGetExperianClosureReport()
    {
        $report = RentalReportFactory::getExperianReport(RentalReportType::CLOSURE, $this->getEntityManagerMock());

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianClosureReport', $report);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Experian report type 'Unknown Experian type' does not exist
     */
    public function shouldThrowExceptionIfUnknownExperianReportTypeSpecified()
    {
        RentalReportFactory::getExperianReport('Unknown Experian type', $this->getEntityManagerMock());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    protected function getEntityManagerMock()
    {
        return $this->getMock('Doctrine\ORM\EntityManagerInterface', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RentalReportData
     */
    protected function getRentalReportDataMock()
    {
        return $this->getMock('RentJeeves\CoreBundle\Report\RentalReportData', [], [], '', false);
    }
}
