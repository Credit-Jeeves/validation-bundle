<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Helpers\HashHeaderCreator;
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CsvFileReader   $csvFileReader
     * @param LoggerInterface $logger
     */
    public function __construct(CsvFileReader $csvFileReader, LoggerInterface $logger)
    {
        $this->csvReader = $csvFileReader;
        $this->csvReader->setUseHeader(false);
        $this->logger = $logger;
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

        if (false === file_exists($this->pathToFile) && false === is_readable($this->pathToFile)) {
            throw new ImportExtractorException(sprintf('File "%s" not found or not readable.', $this->pathToFile));
        }

        $this->logger->info(
            'Starting process CSV extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->pathToFile]
        );

        $csvData = $this->csvReader->read($this->pathToFile);
        $hashHeader = HashHeaderCreator::createHashHeader($csvData[0]);

        unset($csvData[0]); // remove header row

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
