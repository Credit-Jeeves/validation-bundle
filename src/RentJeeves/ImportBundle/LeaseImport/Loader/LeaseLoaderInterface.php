<?php

namespace RentJeeves\ImportBundle\LeaseImport\Loader;

use RentJeeves\DataBundle\Entity\Import;

interface LeaseLoaderInterface
{
    /**
     * Process ImportLease and create project`s Entities
     *
     * @param Import $import
     * @param string $additionalParameter
     */
    public function loadData(Import $import, $additionalParameter = null);
}
