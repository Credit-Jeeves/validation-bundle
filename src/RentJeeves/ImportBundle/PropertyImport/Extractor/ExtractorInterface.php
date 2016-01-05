<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;

interface ExtractorInterface
{
    /**
     * Extract the data from the accounting system for one external Property.
     *
     * @param Group  $group              Group with configs for extract
     * @param string $externalPropertyId external property to extract data for
     *
     * @throws ImportExtractorException if data cannot be extracted from the external accounting system
     *
     * @return array containing one or more accounting system specific model objects
     */
    public function extractData(Group $group, $externalPropertyId);
}
