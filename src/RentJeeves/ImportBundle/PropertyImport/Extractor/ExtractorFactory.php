<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;

/**
 * Service`s name "import.property.extractor_factory"
 */
class ExtractorFactory
{
    /**
     * @var array
     */
    protected $supportedAccountingSystems;

    /**
     * @param MRIExtractor $MRIExtractor
     */
    public function __construct(MRIExtractor $MRIExtractor)
    {
        $this->supportedAccountingSystems = [
            ApiIntegrationType::MRI => $MRIExtractor,
        ];
    }

    /**
     * Get an extractor for the given group.
     *
     * This interface is used in the property import
     *
     * @param string $accountingSystemName
     *
     * @throws ImportInvalidArgumentException if accountingSystem`s name is not supported
     *
     * @return ExtractorInterface
     */
    public function getExtractor($accountingSystemName)
    {
        if (false === in_array($accountingSystemName, array_keys($this->supportedAccountingSystems))) {
            throw new ImportInvalidArgumentException(
                sprintf('Accounting System with name "%s" is not supported.', $accountingSystemName)
            );
        }

        return $this->supportedAccountingSystems[$accountingSystemName];
    }
}
