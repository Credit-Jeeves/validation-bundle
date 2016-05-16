<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\RentManagerSettings;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupGroupTrait;
use RentTrack\RentManagerClientBundle\DataResource\UnitResource;
use RentTrack\RentManagerClientBundle\Exception\RentManagerClientException;
use RentTrack\RentManagerClientBundle\DataResource\PropertyResource;
use RentTrack\RentManagerClientBundle\Model\Property;
use RentTrack\RentManagerClientBundle\Model\RentManagerCredentials;
use RentTrack\RentManagerClientBundle\RentManagerClient;

/**
 * Service`s name "import.property.extractor.rent_manager"
 */
class RentManagerExtractor implements ApiExtractorInterface
{
    use SetupGroupTrait;
    use SetupExternalPropertyIdTrait;

    /**
     * @var RentManagerClient
     */
    protected $rentManagerClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param RentManagerClient $rentManagerClient
     * @param LoggerInterface   $logger
     */
    public function __construct(RentManagerClient $rentManagerClient, LoggerInterface $logger)
    {
        $this->rentManagerClient = $rentManagerClient;
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
            'Starting process RentManager extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        if (!$this->group->getIntegratedApiSettings() instanceof RentManagerSettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for RentManagerExtractor.',
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }

        $credentials = $this->createRentManagerCredentials($this->group->getIntegratedApiSettings());

        try {
            $rentManagerProperties = $this->rentManagerClient->getCollection(
                PropertyResource::RESOURCE_NAME,
                ['filters' => sprintf('ShortName,eq,%s', $this->externalPropertyId)],
                $credentials
            );

            if (true === empty($rentManagerProperties)) {
                $this->logger->warning(
                    sprintf(
                        'RentManagerProperty with short name "%s" not found.',
                        $this->externalPropertyId
                    ),
                    ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
                );

                return [];
            }

            /** @var Property $rentManagerProperty */
            $rentManagerProperty = current($rentManagerProperties);
            $rentManagerUnits = $this->rentManagerClient->getCollection(
                UnitResource::RESOURCE_NAME,
                ['filters' => sprintf('PropertyID,eq,%s', $rentManagerProperty->getPropertyId())],
                $credentials
            );

            if (true === empty($rentManagerUnits)) {
                $this->logger->warning(
                    sprintf(
                        'RentManagerUnits for RentManagerProperty with PropertyID "%s" not found.',
                        $rentManagerProperty->getPropertyId()
                    ),
                    ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
                );

                return [];
            }
        } catch (RentManagerClientException $e) {
            $this->logger->warning(
                $message = sprintf(
                    'Can`t get data from RentManager. Details: %s',
                    $e->getMessage()
                ),
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }

        $this->logger->info(
            'Finished process extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        return [
            'property' => $rentManagerProperty,
            'units' => $rentManagerUnits,
        ];
    }

    /**
     * @param RentManagerSettings $settings
     *
     * @return RentManagerCredentials
     */
    protected function createRentManagerCredentials(RentManagerSettings $settings)
    {
        return new RentManagerCredentials(
            $settings->getCorpid(),
            $settings->getUser(),
            $settings->getPassword(),
            $settings->getLocationId()
        );
    }
}
