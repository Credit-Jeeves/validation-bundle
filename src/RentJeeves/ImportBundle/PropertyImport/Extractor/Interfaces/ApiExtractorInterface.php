<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces;

interface ApiExtractorInterface extends ExtractorInterface
{
    /**
     * Setup external property id which will be use for extract data from API
     *
     * @param mixed $extPropertyId
     */
    public function setExtPropertyId($extPropertyId);
}
