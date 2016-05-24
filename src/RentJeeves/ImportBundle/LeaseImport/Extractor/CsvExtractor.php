<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor;

use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\CsvLeaseExtractorInterface;
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
     * {@inheritdoc}
     */
    public function extractData()
    {
        // TODO: Implement extractData() method.
    }
}
