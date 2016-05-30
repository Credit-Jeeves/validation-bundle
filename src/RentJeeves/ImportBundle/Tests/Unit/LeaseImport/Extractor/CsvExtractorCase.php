<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Sftp\SftpFileManager;
use RentJeeves\ImportBundle\LeaseImport\Extractor\CsvExtractor as LeaseCsvExtractor;
use RentJeeves\ImportBundle\PropertyImport\Extractor\CsvExtractor as PropertyCsvExtractor;
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
    public function shouldThrowExceptionIfExtractorDoesNotConfigure()
    {
        $propertyCsvExtractor = new PropertyCsvExtractor(
            new CsvFileReader(),
            $this->getBaseMock(SftpFileManager::class),
            $this->getLoggerMock()
        );
        $csvLeaseExtractor = new LeaseCsvExtractor($propertyCsvExtractor);
        $csvLeaseExtractor->extractData();
    }

    /**
     * @test
     */
    public function shouldExtractDataFromCsvFile()
    {
        $sftpFileManager = $this->getBaseMock(SftpFileManager::class);
        $sftpFileManager
            ->method('download')
            ->will($this->returnCallback(
                function ($inputFileName, $tmpFileName) {
                    $file = __DIR__ . '/../../../Fixtures/csvExample.csv';
                    copy($file, $tmpFileName);
                }
            ));
        $csvPropertyExtractor = new PropertyCsvExtractor(
            new CsvFileReader(),
            $sftpFileManager,
            $this->getLoggerMock()
        );

        $csvPropertyExtractor->setGroup(new Group());
        $csvPropertyExtractor->setPathToFile(__DIR__ . 'test');

        $csvLeaseExtractor = new LeaseCsvExtractor($csvPropertyExtractor);
        $result = $csvLeaseExtractor->extractData();

        $this->assertEquals(3, count($result['data']), 'File contains 8 row with data.');
        $this->assertNotEmpty($result['hashHeader'], 'HashHeader should be not empty.');
    }

}
