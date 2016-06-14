<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\ApiLeaseExtractorInterface;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\CsvLeaseExtractorInterface;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\LeaseExtractorInterface;

/**
 * Service`s name "import.lease.extractor_builder"
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
     * @throws ImportLogicException
     *
     * @return LeaseExtractorInterface
     */
    public function build()
    {
        if (null === $this->group || null === $this->additionalParameter) {
            throw new ImportLogicException(
                'Cant run build without required params. Pls use "setGroup" and "setAdditionalParameter".'
            );
        }

        $this->logger->debug(
            'Try build extractor.',
            ['group' => $this->group, 'additional_parameter' => $this->additionalParameter]
        );

        $extractor = $this->factory->getExtractor($this->group);
        $extractor->setGroup($this->group);
        if ($extractor instanceof ApiLeaseExtractorInterface) {
            $extractor->setExternalPropertyId($this->additionalParameter);
        } elseif ($extractor instanceof CsvLeaseExtractorInterface) {
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
