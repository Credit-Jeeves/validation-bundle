<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\CsvExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupGroupTrait;

/**
 * Service`s name "import.property.extractor.csv"
 */
class CsvExtractor implements CsvExtractorInterface
{
    use SetupGroupTrait;

    protected $pathToFile;

    public function extractData()
    {
        // TODO: Implement extractData() method.
    }

    public function setPathToFile($pathToFile)
    {
        // TODO: Implement setPathToFile() method.
    }
}
