<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces;

interface CsvExtractorInterface extends ExtractorInterface
{
    /**
     * Setup path to CSV file which will be use for extract
     *
     * @param mixed $pathToFile
     */
    public function setPathToFile($pathToFile);
}
