<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Sftp\SftpFileManager;
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
        $csvExtractor = new CsvExtractor(
            new CsvFileReader(),
            $this->getBaseMock(SftpFileManager::class),
            $this->getLoggerMock()
        );
        $csvExtractor->extractData();
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
        $csvExtractor = new CsvExtractor(
            new CsvFileReader(),
            $sftpFileManager,
            $this->getLoggerMock()
        );

        $csvExtractor->setGroup(new Group());
        $csvExtractor->setPathToFile(__DIR__ . 'test');
        $result = $csvExtractor->extractData();

        $this->assertEquals(3, count($result['data']), 'File contains 3 row with data.');
        $this->assertNotEmpty($result['hashHeader'], 'HashHeader should be not empty.');
    }
}
