<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces;

interface ApiLeaseExtractorInterface extends LeaseExtractorInterface
{
    /**
     * Setup external property id which will be use for extract lease data from API
     *
     * @param string $extPropertyId
     */
    public function setExtPropertyId($extPropertyId);
}
