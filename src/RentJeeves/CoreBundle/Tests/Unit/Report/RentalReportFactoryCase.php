<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Report;

use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\RentalReportFactory;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class RentalReportFactoryCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

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
     * @test
     */
    public function shouldGetEquifaxPositiveReport()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::EQUIFAX, RentalReportType::POSITIVE);

        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Equifax\EquifaxPositiveReport', $report);
    }

    /**
     * @test
     */
    public function shouldGetEquifaxClosureReport()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::EQUIFAX, RentalReportType::CLOSURE);
        $report = $this->getRentalReportFactory()->getReport($rentalReportData);

        $this->assertInstanceOf('RentJeeves\CoreBundle\Report\Equifax\EquifaxClosureReport', $report);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Equifax report type 'Unknown Equifax type' does not exist
     */
    public function shouldThrowExceptionIfUnknownEquifaxReportTypeSpecified()
    {
        $rentalReportData = $this->getRentalReportData(CreditBureau::EQUIFAX, 'Unknown Equifax type');

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
}
