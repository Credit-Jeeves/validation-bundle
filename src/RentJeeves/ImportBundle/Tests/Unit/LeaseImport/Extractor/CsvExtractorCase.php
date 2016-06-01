<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\LeaseImport\Extractor\CsvExtractor as LeaseCsvExtractor;
use RentJeeves\ImportBundle\PropertyImport\Extractor\CsvExtractor as PropertyCsvExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class CsvExtractorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldExtractData()
    {
        $propertyCsvExtractorMock = $this->getBaseMock(PropertyCsvExtractor::class);
        $propertyCsvExtractorMock->expects($this->once())
            ->method('setGroup');
        $propertyCsvExtractorMock->expects($this->once())
            ->method('setPathToFile');
        $propertyCsvExtractorMock->expects($this->once())
            ->method('extractData')
            ->willReturn(['hashHeader' => [], 'data' => []]);

        $csvLeaseExtractor = new LeaseCsvExtractor($propertyCsvExtractorMock);
        $csvLeaseExtractor->setGroup(new Group());
        $csvLeaseExtractor->setPathToFile(__DIR__ . 'test');
        $result = $csvLeaseExtractor->extractData();

        $this->arrayHasKey('hashHeader', $result, 'Should return array with key hashHeader');
        $this->arrayHasKey('data', $result, 'Should return array with key data');
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Pls configure extractor("setGroup","setPathToFile") before extractData.
     */
    public function shouldThrowExceptionIfExtractorDoesNotConfigure()
    {
        $propertyCsvExtractorMock = $this->getBaseMock(PropertyCsvExtractor::class);
        $propertyCsvExtractorMock->expects($this->never())
            ->method('setGroup');
        $propertyCsvExtractorMock->expects($this->never())
            ->method('setPathToFile');
        $propertyCsvExtractorMock->expects($this->once())
            ->method('extractData')
            ->willThrowException(
                new ImportExtractorException('Pls configure extractor("setGroup","setPathToFile") before extractData.')
            );

        $csvLeaseExtractor = new LeaseCsvExtractor($propertyCsvExtractorMock);
        $csvLeaseExtractor->extractData();
    }
}
