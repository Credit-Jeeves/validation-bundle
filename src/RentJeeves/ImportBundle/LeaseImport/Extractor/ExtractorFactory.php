<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\ApiLeaseExtractorInterface;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\CsvLeaseExtractorInterface;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\LeaseExtractorInterface;

/**
 * Service`s name "import.lease.extractor_factory"
 */
class ExtractorFactory
{
    /**
     * @var array Assoc array, where
     *  key = AccountingSystem`s name
     *  value = service which implements ApiLeaseExtractorInterface
     */
    protected $supportedApiExtractors = [];

    /**
     * @var CsvLeaseExtractorInterface
     */
    protected $csvExtractor;

    /**
     * @param CsvLeaseExtractorInterface $csvExtractor
     */
    public function __construct(CsvLeaseExtractorInterface $csvExtractor)
    {
        $this->csvExtractor = $csvExtractor;
    }

    /**
     * @param string                     $accountingSystemName
     * @param ApiLeaseExtractorInterface $extractor
     *
     * @throws ImportInvalidArgumentException input data is not valid
     */
    public function addApiExtractor($accountingSystemName, ApiLeaseExtractorInterface $extractor)
    {
        if (false === in_array($accountingSystemName, AccountingSystem::$integratedWithApi)) {
            throw new ImportInvalidArgumentException(
                sprintf(
                    'ExtractorFactory: "%s" is not valid Api Accounting System Name.',
                    $accountingSystemName
                )
            );
        }

        $this->supportedApiExtractors[$accountingSystemName] = $extractor;
    }

    /**
     * Get an lease extractor for the given group.
     *
     * @param Group $group
     *
     * @throws ImportInvalidArgumentException Group has incorrect settings for import
     *
     * @return LeaseExtractorInterface
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
