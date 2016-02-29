<?php

namespace RentJeeves\ImportBundle\PropertyImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;

/**
 * Service`s name "import.property.loader_factory"
 */
class LoaderFactory
{
    /**
     * @var MappedLoader
     */
    protected $mappedLoader;

    /**
     * @var UnmappedLoader
     */
    protected $unmappedLoader;

    /**
     * @param MappedLoader   $mappedLoader
     * @param UnmappedLoader $unmappedLoader
     */
    public function __construct(MappedLoader $mappedLoader, UnmappedLoader $unmappedLoader)
    {
        $this->mappedLoader = $mappedLoader;
        $this->unmappedLoader = $unmappedLoader;
    }

    /**
     * @param Group $group
     *
     * @throws ImportInvalidArgumentException
     *
     * @return PropertyLoaderInterface
     */
    public function getLoader(Group $group)
    {
        if (null === $importSettings = $group->getCurrentImportSettings()) {
            throw new ImportInvalidArgumentException(
                sprintf('Group#%d doesn`t have settings for import.', $group->getId())
            );
        }

        if (ImportSource::CSV === $importSettings->getSource()) {
            return $this->unmappedLoader;
        } else {
            return $this->mappedLoader;
        }
    }
}
