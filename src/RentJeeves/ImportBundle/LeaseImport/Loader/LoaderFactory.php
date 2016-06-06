<?php

namespace RentJeeves\ImportBundle\LeaseImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;

/**
 * Service`s name "import.lease.loader_factory"
 */
class LoaderFactory
{
    /**
     * @var BaseLoader
     */
    protected $baseLoader;

    /**
     * @var CsvLoader
     */
    protected $csvLoader;

    /**
     * @param BaseLoader $baseLoader
     * @param CsvLoader  $csvLoader
     */
    public function __construct(BaseLoader $baseLoader, CsvLoader $csvLoader)
    {
        $this->baseLoader = $baseLoader;
        $this->csvLoader = $csvLoader;
    }

    /**
     * @param Group $group
     *
     * @throws ImportInvalidArgumentException if Group doesn`t have import settings
     *
     * @return LeaseLoaderInterface
     */
    public function getLoader(Group $group)
    {
        if (null === $importSettings = $group->getCurrentImportSettings()) {
            throw new ImportInvalidArgumentException(
                sprintf('Group#%d doesn`t have settings for import.', $group->getId())
            );
        }

        if (ImportSource::CSV === $importSettings->getSource()) {
            return $this->csvLoader;
        } else {
            return $this->baseLoader;
        }
    }
}
