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
     * @var Group
     */
    protected $group;

    /**
     * @param YardiResidentDataManager $residentDataManager
     * @param LoggerInterface $logger
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
            sprintf(
                'Starting process Yardi extractData for extPropertyId#%s',
                $this->externalPropertyId
            ),
            ['group_id' => $this->group->getId()]
        );

        if (!$this->group->getIntegratedApiSettings() instanceof YardiSettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for YardiExtractor.',
                ['group_id' => $this->group->getId()]
            );

            throw new ImportExtractorException($message);
        }

        $this->residentDataManager->setSettings($this->group->getIntegratedApiSettings());

        try {
            $data = $this->getFullResidentsList($externalPropertyId);
        } catch (\Exception $e) {
            $this->logger->warning(
                $message = sprintf(
                    'Can`t get data from Yardi for externalPropertyID "%s". Details: %s',
                    $externalPropertyId,
                    $e->getMessage()
                ),
                ['group_id' => $this->group->getId()]
            );

            throw new ImportExtractorException($message);
        }

        if (empty($data)) {
            $this->logger->info(
                sprintf(
                    'Returned response for externalPropertyID#%s is empty.',
                    $externalPropertyId
                ),
                ['group_id' => $this->group->getId()]
            );
        }

        $this->logger->info(
            sprintf(
                'Finished process extractData for externalPropertyID#%s',
                $externalPropertyId
            ),
            ['group_id' => $this->group->getId()]
        );

        return $data;
    }

    /**
     * @param $externalPropertyId
     * @return array
     * @throws ImportExtractorException
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
     * @return Property
     * @throws ImportExtractorException
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
     * @return \RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident[]
     * @throws ImportExtractorException
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
     * @param Property $property
     * @param ResidentsResident $resident
     * @return \RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile
     * @throws ImportExtractorException
     */
    protected function getResidentData(Property $property, ResidentsResident $resident)
    {
        try {
            return $this->residentDataManager->getResidentData(
                $resident->getCode(),
                $property->getCode()
            );
        } catch (\Exception $e) {
            $message = sprintf(
                'Can\'t get resident data for residentID:%s externalPropertyID: %s error: %s',
                $resident->getCode(),
                $property->getCode(),
                $e->getMessage()
            );
            $this->logger->alert($message, ['group_id' => $this->group->getId()]);

            throw new ImportExtractorException($message);
        }
    }
}
