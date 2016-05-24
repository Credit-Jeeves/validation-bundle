<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces;

interface CsvLeaseExtractorInterface extends LeaseExtractorInterface
{
    /**
     * Setup path to CSV file which will be use for extract lease data
     *
     * @param string $pathToFile
     */
    public function setPathToFile($pathToFile);
}
