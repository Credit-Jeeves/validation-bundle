<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Helpers\HashHeaderCreator;
use RentJeeves\CoreBundle\Sftp\SftpFileManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\CsvExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupGroupTrait;

/**
 * Service`s name "import.property.extractor.csv"
 */
class CsvExtractor implements CsvExtractorInterface
{
    use SetupGroupTrait;

    /**
     * @var string
     */
    protected $pathToFile;

    /**
     * @var CsvFileReader
     */
    protected $csvReader;

    /**
     * @var SftpFileManager
     */
    protected $sftpFileManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CsvFileReader   $csvFileReader
     * @param SftpFileManager $sftpFileManager
     * @param LoggerInterface $logger
     */
    public function __construct(CsvFileReader $csvFileReader, SftpFileManager $sftpFileManager, LoggerInterface $logger)
    {
        $this->csvReader = $csvFileReader;
        $this->csvReader->setUseHeader(false);
        $this->logger = $logger;
        $this->sftpFileManager = $sftpFileManager;
    }

    /**
     * @throws ImportExtractorException incorrect path to file or file not readable|Incorrect config for extractor
     *
     * @return array
     */
    public function extractData()
    {
        if (null === $this->group || null === $this->pathToFile) {
            throw new ImportExtractorException(
                'Pls configure extractor("setGroup","setPathToFile") before extractData.'
            );
        }

        $this->logger->info(
            'Starting process CSV extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->pathToFile]
        );

        $tmpFile = sprintf('%s%s%s.csv', sys_get_temp_dir(), DIRECTORY_SEPARATOR, uniqid());

        $this->sftpFileManager->download($this->pathToFile, $tmpFile);

        $csvData = $this->csvReader->read($tmpFile);
        $hashHeader = HashHeaderCreator::createHashHeader($csvData[0]);

        unset($csvData[0]); // remove header row
        unlink($tmpFile); // remove tmp file

        return [
            'hashHeader' => $hashHeader,
            'data' => array_values($csvData),
        ];
    }

    /**
     * @param string $pathToFile
     */
    public function setPathToFile($pathToFile)
    {
        $this->pathToFile = $pathToFile;
    }
}
