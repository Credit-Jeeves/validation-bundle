<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\CsvExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ExtractorInterface as Extractor;

/**
 * Service`s name "import.property.extractor_factory"
 */
class ExtractorFactory
{
    /**
     * @var array Assoc array, where
     *  key = AccountingSystem`s name
     *  value = service which implements ApiExtractorInterface
     */
    protected $supportedApiExtractors;

    /**
     * @var CsvExtractorInterface
     */
    protected $csvExtractor;

    /**
     * @param array                 $supportedApiExtractors
     * @param CsvExtractorInterface $csvExtractor
     */
    public function __construct(array $supportedApiExtractors, CsvExtractorInterface $csvExtractor)
    {
        $this->supportedApiExtractors = $supportedApiExtractors;
        $this->csvExtractor = $csvExtractor;
    }

    /**
     * Get an extractor for the given group.
     *
     * This interface is used in the property import
     *
     * @param Group $group
     *
     * @throws ImportInvalidArgumentException group has incorrect settings for import
     *
     * @return Extractor
     */
    public function getExtractor(Group $group)
    {
        if (null === $importSettings = $group->getCurrentImportSettings()) {
            throw new ImportInvalidArgumentException(
                sprintf('Group#%d doesn`t have settings for import.', $group->getId())
            );
        }

        if (ImportSource::CSV === $importSettings->getSource()) {
            return $this->csvExtractor;
        } else {
            $accountingSystemName = $group->getHolding()->getAccountingSystem();

            if (false === in_array($accountingSystemName, array_keys($this->supportedApiExtractors))) {
                throw new ImportInvalidArgumentException(
                    sprintf(
                        'ExtractorFactory: Accounting System with name "%s" is not supported.',
                        $accountingSystemName
                    )
                );
            }

            return $this->supportedApiExtractors[$accountingSystemName];
        }
    }
}
