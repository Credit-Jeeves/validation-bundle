<?php

namespace RentJeeves\ImportBundle\PropertyImport\Loader;

use RentJeeves\DataBundle\Entity\Import;

/**
 * Service`s name "import.property.loader.unmapped"
 */
class UnmappedLoader implements PropertyLoaderInterface
{
    public function loadData(Import $import, $externalPropertyId = null)
    {
        // TODO: Implement loadData() method.
    }
}
