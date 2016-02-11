<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ImportBundle\PropertyImport\Extractor\AMSIExtractor;
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
        $factory = new ExtractorFactory([AccountingSystem::MRI => $this->getMriExtractorMock()]);
        $factory->getExtractor('test');
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
        $factory = new ExtractorFactory(
            [
                AccountingSystem::MRI => $this->getMriExtractorMock(),
                AccountingSystem::YARDI_VOYAGER => $this->getYardiExtractorMock(),
                AccountingSystem::AMSI => $this->getAMSIExtractorMock(),
                AccountingSystem::RESMAN => $this->getResmanExtractorMock(),
            ]
        );
        $extractor = $factory->getExtractor($extractorName);

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
}
