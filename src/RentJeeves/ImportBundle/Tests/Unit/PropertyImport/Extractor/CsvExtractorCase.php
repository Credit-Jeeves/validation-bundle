<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\ImportBundle\PropertyImport\Extractor\CsvExtractor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class CsvExtractorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage Pls configure extractor("setGroup","setPathToFile") before extractData.
     */
    public function shouldThrowExceptionIfExtractorNotConfigured()
    {
        $csvExtractor = new CsvExtractor(new CsvFileReader(), $this->getLoggerMock());
        $csvExtractor->extractData();
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportExtractorException
     * @expectedExceptionMessage File "test.test" not found or not readable.
     */
    public function shouldThrowExceptionIfPathToFileNotCorrect()
    {
        $csvExtractor = new CsvExtractor(new CsvFileReader(), $this->getLoggerMock());
        $csvExtractor->setGroup(new Group());
        $csvExtractor->setPathToFile('test.test');
        $csvExtractor->extractData();
    }

    /**
     * @test
     */
    public function shouldExtractDataFromCsvFile()
    {
        $csvExtractor = new CsvExtractor(new CsvFileReader(), $this->getLoggerMock());
        $csvExtractor->setGroup(new Group());
        $csvExtractor->setPathToFile(__DIR__ . '/../../../Fixtures/csvExample.csv');
        $result = $csvExtractor->extractData();

        $this->assertEquals(3, count($result['data']), 'File contains 8 row with data.');
        $this->assertNotEmpty($result['hashHeader'], 'HashHeader should be not empty.');
    }
}
