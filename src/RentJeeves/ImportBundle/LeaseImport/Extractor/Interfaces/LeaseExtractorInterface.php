<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;

interface LeaseExtractorInterface
{
    /**
     * Extract lease data from the accounting system (API or CSV file).
     *
     * @throws ImportExtractorException if data cannot be extracted from the external accounting system
     *
     * @return array containing one or more accounting system specific model objects
     */
    public function extractData();

    /**
     * Set up a group to run the import for
     *
     * @param Group $group
     */
    public function setGroup(Group $group);
}
