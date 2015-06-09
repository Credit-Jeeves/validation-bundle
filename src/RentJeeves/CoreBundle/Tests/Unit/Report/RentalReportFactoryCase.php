<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Report;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
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
        $rentalReportData = $this->getRentalReportData(CreditBureau::EXPERIAN, RentalReportType::POSITIVE);

        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\RentalReport', $report);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Given report bureau 'Unknown bureau' does not exist
     */
    public function shouldThrowExceptionIfUnknownBureauSpecified()
    {
        $rentalReportData = $this->getRentalReportData('Unknown bureau', RentalReportType::POSITIVE);

        $this->getRentalReportFactory()->getReport($rentalReportData);
    }

    /**
     * @test
     */
    public function shouldGetTransUnionPositiveReport()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::TRANS_UNION, RentalReportType::POSITIVE);

        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionPositiveReport', $report);
    }

    /**
     * @test
     */
    public function shouldGetTransUnionNegativeReport()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::TRANS_UNION, RentalReportType::NEGATIVE);
        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionNegativeReport', $report);
    }

    /**
     * @test
     */
    public function shouldGetTransUnionClosureReport()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::TRANS_UNION, RentalReportType::CLOSURE);
        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\TransUnion\TransUnionClosureReport', $report);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage TransUnion report type 'Unknown TransUnion type' does not exist
     */
    public function shouldThrowExceptionIfUnknownTransUnionReportTypeSpecified()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::TRANS_UNION, 'Unknown TransUnion type');

        $this->getRentalReportFactory()->getReport($rentalReportData);
    }

    /**
     * @test
     */
    public function shouldGetExperianPositiveReport()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::EXPERIAN, RentalReportType::POSITIVE);
        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianPositiveReport', $report);
    }

    /**
     * @test
     */
    public function shouldGetExperianClosureReport()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::EXPERIAN, RentalReportType::CLOSURE);
        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Experian\ExperianClosureReport', $report);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Experian report type 'Unknown Experian type' does not exist
     */
    public function shouldThrowExceptionIfUnknownExperianReportTypeSpecified()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::EXPERIAN, 'Unknown Experian type');
        $this->getRentalReportFactory()->getReport($rentalReportData);
    }

    /**
     * @return RentalReportFactory
     */
    protected function getRentalReportFactory()
    {
        return new RentalReportFactory($this->getEntityManagerMock(), $this->getLoggerMock(), []);
    }

    /**
     * @param string $bureau
     * @param string $type
     * @return RentalReportData
     */
    protected function getRentalReportData($bureau, $type)
    {
        $rentalReportData = new RentalReportData();
        $rentalReportData->setBureau($bureau);
        $rentalReportData->setType($type);

        return $rentalReportData;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    protected function getEntityManagerMock()
    {
        return $this->getMock('Doctrine\ORM\EntityManagerInterface', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('Monolog\Logger', [], [], '', false);
    }
}
