<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\CsvExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ExtractorInterface;

/**
 * Service`s name "import.property.extractor_builder"
 */
class ExtractorBuilder
{
    /**
     * @var ExtractorFactory
     */
    protected $factory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string path to file|external property id
     */
    protected $additionalParameter;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @param ExtractorFactory $factory
     * @param LoggerInterface  $logger
     */
    public function __construct(ExtractorFactory $factory, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->logger = $logger;
    }

    /**
     * @return ExtractorInterface
     */
    public function build()
    {
        $extractor = $this->factory->getExtractor($this->group);
        $extractor->setGroup($this->group);
        if ($extractor instanceof ApiExtractorInterface) {
            $extractor->setExtPropertyId($this->additionalParameter);
        } elseif ($extractor instanceof CsvExtractorInterface) {
            $extractor->setPathToFile($this->additionalParameter);
        }

        return $extractor;
    }

    /**
     * @param string $additionalParameter
     *
     * @return self
     */
    public function setAdditionalParameter($additionalParameter)
    {
        $this->additionalParameter = $additionalParameter;

        return $this;
    }

    /**
     * @param Group $group
     *
     * @return self
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }
}
