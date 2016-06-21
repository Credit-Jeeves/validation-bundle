<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces;

interface CsvPropertyExtractorInterface extends PropertyExtractorInterface
{
    /**
     * Setup path to CSV file which will be use for extract
     *
     * @param mixed $pathToFile
     */
    public function setPathToFile($pathToFile);
}
