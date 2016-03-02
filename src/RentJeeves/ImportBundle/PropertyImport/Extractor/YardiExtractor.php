<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager as YardiResidentDataManager;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupGroupTrait;

/**
 * Service`s name "import.property.extractor.yardi"
 */
class YardiExtractor implements ApiExtractorInterface
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
                'Pls configure extractor("setGroup","setExtPropertyId") before extractData.'
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
            $data = $this->getFullResidentsList($this->externalPropertyId);
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
     * @return FullResident[]
     */
    protected function getFullResidentsList($externalPropertyId)
    {
        $property = $this->getProperty($externalPropertyId);
        $residents = $this->getResidents($property);
        $listOfFullResident = [];
        /** @var ResidentsResident $resident */
        foreach ($residents as $resident) {
            $fullResident = new FullResident();
            $fullResident->setProperty($property);
            $fullResident->setResident($resident);
            $fullResident->setResidentData($this->getResidentData($property, $resident));

            $listOfFullResident[] = $fullResident;
        }

        return $listOfFullResident;
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
     * @param Property $property
     *
     * @throws ImportExtractorException
     *
     * @return \RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident[]
     */
    protected function getResidents(Property $property)
    {
        $residents = $this->residentDataManager->getResidents($property->getCode());
        if (empty($residents)) {
            throw new ImportExtractorException(
                sprintf('Can\'t find residents by externalPropertyID "%s"', $property->getCode())
            );
        }

        return $residents;
    }

    /**
     * @param Property          $property
     * @param ResidentsResident $resident
     *
     * @throws ImportExtractorException
     *
     * @return \RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile
     */
    protected function getResidentData(Property $property, ResidentsResident $resident)
    {
        try {
            return $this->residentDataManager->getResidentData($resident->getCode(), $property->getCode());
        } catch (\Exception $e) {
            $message = sprintf(
                'Can\'t get resident data for residentID:%s externalPropertyID: %s error: %s',
                $resident->getCode(),
                $property->getCode(),
                $e->getMessage()
            );
            $this->logger->alert(
                $message,
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }
    }
}
