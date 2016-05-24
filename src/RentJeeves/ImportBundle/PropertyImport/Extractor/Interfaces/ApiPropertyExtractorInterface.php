<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces;

interface ApiPropertyExtractorInterface extends PropertyExtractorInterface
{
    /**
     * Setup external property id which will be use for extract data from API
     *
     * @param mixed $extPropertyId
     */
    public function setExtPropertyId($extPropertyId);
}
