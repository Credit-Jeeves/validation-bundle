<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager as YardiResidentDataManager;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiPropertyExtractorInterface;
use RentJeeves\ImportBundle\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\Traits\SetupGroupTrait;

/**
 * Service`s name "import.property.extractor.yardi"
 */
class YardiExtractor implements ApiPropertyExtractorInterface
{
    use SetupGroupTrait;
    use SetupExternalPropertyIdTrait;

    /**
     * @var YardiResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param YardiResidentDataManager $residentDataManager
     * @param LoggerInterface          $logger
     */
    public function __construct(YardiResidentDataManager $residentDataManager, LoggerInterface $logger)
    {
        $this->residentDataManager = $residentDataManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function extractData()
    {
        if (null === $this->group || null === $this->externalPropertyId) {
            throw new ImportLogicException(
                'Pls configure extractor("setGroup","setExternalPropertyId") before extractData.'
            );
        }
        $this->logger->info(
            'Starting process Yardi extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        if (!$this->group->getIntegratedApiSettings() instanceof YardiSettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for YardiExtractor.',
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }

        $this->residentDataManager->setSettings($this->group->getIntegratedApiSettings());

        try {
            $data = $this->getUnitList($this->externalPropertyId);
        } catch (\Exception $e) {
            $this->logger->warning(
                $message = sprintf(
                    'Can`t get data from Yardi. Details: %s',
                    $e->getMessage()
                ),
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }

        if (empty($data)) {
            $this->logger->info(
                'Returned response is empty.',
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );
        }

        $this->logger->info(
            'Finished process extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        return $data;
    }

    /**
     * @param string $externalPropertyId
     *
     * @throws ImportExtractorException
     *
     * @return UnitInformation[]
     */
    protected function getUnitList($externalPropertyId)
    {
        $property = $this->getProperty($externalPropertyId);
        $customers = $this->getPropertyCustomerUnits();
        $listOfUnit = [];
        foreach ($customers as $customer) {
            $unitInformation = new UnitInformation();
            $unitInformation->setProperty($property);
            $unitInformation->setUnit($customer->getUnit());
            $listOfUnit[] = $unitInformation;
        }

        return $listOfUnit;
    }

    /**
     * @param string $externalPropertyId
     *
     * @throws ImportExtractorException
     *
     * @return Property
     */
    protected function getProperty($externalPropertyId)
    {
        $properties = $this->residentDataManager->getProperties();
        $filteredProperties = array_filter(
            $properties,
            function (Property $property) use ($externalPropertyId) {
                return $externalPropertyId === $property->getCode();
            }
        );

        if (empty($filteredProperties)) {
            throw new ImportExtractorException(
                sprintf(
                    'Can\'t find property by externalPropertyID "%s" in property configurations',
                    $externalPropertyId
                )
            );
        }

        return reset($filteredProperties);
    }

    /**
     * @return \RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationCustomer[]
     * @throws ImportExtractorException
     */
    protected function getPropertyCustomerUnits()
    {
        $customerUnits = $this->residentDataManager->getPropertyCustomerUnits($this->externalPropertyId);
        if (empty($customerUnits)) {
            throw new ImportExtractorException(
                sprintf('No property units data found for Yardi property(%s)', $this->externalPropertyId)
            );
        }

        return $customerUnits;
    }
}
