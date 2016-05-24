<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor;

use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\ApiLeaseExtractorInterface;
use RentJeeves\ImportBundle\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\Traits\SetupGroupTrait;

/**
 * Service`s name "import.lease.extractor.resman"
 */
class ResmanExtractor implements ApiLeaseExtractorInterface
{
    use SetupGroupTrait;
    use SetupExternalPropertyIdTrait;

    /**
     * {@inheritdoc}
     */
    public function extractData()
    {
        // TODO: Implement extractData() method.
    }
}
