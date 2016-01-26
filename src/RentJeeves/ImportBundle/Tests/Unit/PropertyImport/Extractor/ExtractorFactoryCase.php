<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorFactory;
use RentJeeves\ImportBundle\PropertyImport\Extractor\MRIExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ExtractorFactoryCase extends UnitTestBase
{
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
        $factory = new ExtractorFactory([AccountingSystem::MRI => $this->getMriExtractorMock()]);
        $extractor = $factory->getExtractor($extractorName);

        $this->assertInstanceOf($expectedExtractor, $extractor, 'Incorrect instance of Extractor.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MRIExtractor
     */
    protected function getMriExtractorMock()
    {
        return $this->getMock(
            '\RentJeeves\ImportBundle\PropertyImport\Extractor\MRIExtractor',
            [],
            [],
            '',
            false
        );
    }
}
