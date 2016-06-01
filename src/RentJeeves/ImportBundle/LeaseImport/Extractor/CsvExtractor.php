<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor;

use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\CsvLeaseExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\CsvExtractor as ImportPropertyCsvExtractor;
use RentJeeves\ImportBundle\Traits\SetupGroupTrait;
use RentJeeves\ImportBundle\Traits\SetupPathToImportFile;

/**
 * Service`s name "import.lease.extractor.csv"
 */
class CsvExtractor implements CsvLeaseExtractorInterface
{
    use SetupGroupTrait;
    use SetupPathToImportFile;

    /**
     * @var ImportPropertyCsvExtractor
     */
    protected $importPropertyCsvExtractor;

    /**
     * @param ImportPropertyCsvExtractor $importPropertyCsvExtractor
     */
    public function __construct(ImportPropertyCsvExtractor $importPropertyCsvExtractor)
    {
        $this->importPropertyCsvExtractor = $importPropertyCsvExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function extractData()
    {
        if ($this->group) {
            $this->importPropertyCsvExtractor->setGroup($this->group);
        }

        if ($this->pathToFile) {
            $this->importPropertyCsvExtractor->setPathToFile($this->pathToFile);
        }

        return $this->importPropertyCsvExtractor->extractData();
    }
}
