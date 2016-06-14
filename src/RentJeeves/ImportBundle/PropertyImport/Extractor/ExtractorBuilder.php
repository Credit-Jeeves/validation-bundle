<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiPropertyExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\CsvPropertyExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\PropertyExtractorInterface as Extractor;

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
     * @return Extractor
     */
    public function build()
    {
        $extractor = $this->factory->getExtractor($this->group);
        $extractor->setGroup($this->group);
        if ($extractor instanceof ApiPropertyExtractorInterface) {
            $extractor->setExternalPropertyId($this->additionalParameter);
        } elseif ($extractor instanceof CsvPropertyExtractorInterface) {
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
