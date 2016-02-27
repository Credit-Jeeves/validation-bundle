<?php

namespace RentJeeves\ImportBundle\PropertyImport\Loader;

use RentJeeves\DataBundle\Entity\Import;

interface PropertyLoaderInterface
{
    /**
     * Process ImportProperties and create project`s Entities
     *
     * @param Import $import
     * @param string|null $externalPropertyId
     */
    public function loadData(Import $import, $externalPropertyId = null);
}
