<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\PropertyImport\Extractor\AMSIExtractor;
use RentJeeves\ImportBundle\PropertyImport\Extractor\CsvExtractor;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorFactory;
use RentJeeves\ImportBundle\PropertyImport\Extractor\MRIExtractor;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ResmanExtractor;
use RentJeeves\ImportBundle\PropertyImport\Extractor\YardiExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ExtractorFactoryCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException
     * @expectedExceptionMessage Accounting System with name "test" is not supported
     */
    public function shouldThrowExceptionIfGetNotExistExtractor()
    {
        $holding = new Holding();
        $holding->setAccountingSystem('test');
        $group = new Group();
        $group->setHolding($holding);
        $importGroupSettings = new ImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($importGroupSettings);

        $factory = new ExtractorFactory(
            [AccountingSystem::MRI => $this->getMriExtractorMock()],
            $this->getCsvExtractorMock()
        );
        $factory->getExtractor($group);
    }

    /**
     * @return array
     */
    public function getExtractors()
    {
        return [
            [AccountingSystem::MRI, '\RentJeeves\ImportBundle\PropertyImport\Extractor\MRIExtractor'],
            [AccountingSystem::YARDI_VOYAGER, '\RentJeeves\ImportBundle\PropertyImport\Extractor\YardiExtractor'],
            [AccountingSystem::AMSI, '\RentJeeves\ImportBundle\PropertyImport\Extractor\AMSIExtractor'],
            [AccountingSystem::RESMAN, '\RentJeeves\ImportBundle\PropertyImport\Extractor\ResmanExtractor'],
        ];
    }

    /**
     * @test
     * @dataProvider getExtractors
     *
     * @param string $extractorName
     * @param string $expectedExtractor
     */
    public function shouldReturnCorrectExtractor($extractorName, $expectedExtractor)
    {
        $holding = new Holding();
        $holding->setAccountingSystem($extractorName);
        $group = new Group();
        $group->setHolding($holding);
        $importGroupSettings = new ImportGroupSettings();
        $importGroupSettings->setSource(ImportSource::INTEGRATED_API);
        $group->setImportSettings($importGroupSettings);

        $factory = new ExtractorFactory(
            [
                AccountingSystem::MRI => $this->getMriExtractorMock(),
                AccountingSystem::YARDI_VOYAGER => $this->getYardiExtractorMock(),
                AccountingSystem::AMSI => $this->getAMSIExtractorMock(),
                AccountingSystem::RESMAN => $this->getResmanExtractorMock(),
            ],
            $this->getCsvExtractorMock()
        );
        $extractor = $factory->getExtractor($group);

        $this->assertInstanceOf($expectedExtractor, $extractor, 'Incorrect instance of Extractor.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MRIExtractor
     */
    protected function getMriExtractorMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Extractor\MRIExtractor');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|YardiExtractor
     */
    protected function getYardiExtractorMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Extractor\YardiExtractor');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AMSIExtractor
     */
    protected function getAMSIExtractorMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Extractor\AMSIExtractor');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResmanExtractor
     */
    protected function getResmanExtractorMock()
    {
        return $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\Extractor\ResmanExtractor');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CsvExtractor
     */
    protected function getCsvExtractorMock()
    {
        return $this->getBaseMock(CsvExtractor::class);
    }
}
