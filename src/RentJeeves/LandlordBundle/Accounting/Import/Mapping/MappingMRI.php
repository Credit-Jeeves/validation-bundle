<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Services\MRI\ResidentDataManager;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageMRI;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;
use Symfony\Component\Security\Core\SecurityContext;

class MappingMRI extends MappingCsv
{
    /**
     * @var $residentDataManager ResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var $settings MRISettings
     */
    protected $settings;

    /**
     * @param StorageMRI $storage
     * @param CsvFileReaderImport $reader
     * @param ResidentDataManager $residentDataManager
     * @param SecurityContext $securityContext
     * @throws ImportMappingException
     */
    public function __construct(
        StorageMRI $storage,
        CsvFileReaderImport $reader,
        ResidentDataManager $residentDataManager,
        SecurityContext $securityContext
    ) {
        $this->storage = $storage;
        $this->reader = $reader;
        $this->residentDataManager = $residentDataManager;
        $holding = $securityContext->getToken()->getUser()->getHolding();

        if (empty($holding)) {
            throw new ImportMappingException(
                sprintf(
                    "User(id:%s) don't have holding",
                    $securityContext->getToken()->getUser()->getId()
                )
            );
        }

        $this->settings = $holding->getMriSettings();

        if (empty($this->settings)) {
            throw new ImportMappingException(
                sprintf(
                    "Holding(id:%s) doesn't have MRI Settings",
                    $securityContext->getToken()->getUser()->getHolding()->getId()
                )
            );
        }
    }

    /**
     * @return string
     */
    public function getNextPageLink()
    {
        return $this->residentDataManager->getNextPageLink();
    }

    public function isNeedManualMapping()
    {
        return false;
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    public function isSkipped(array $row)
    {
        return false;
    }

    /**
     * @param string $nextPageLink
     * @return array
     */
    public function getResidentsByNextPageLink($nextPageLink)
    {
        $this->residentDataManager->setSettings($this->settings);
        $residents = $this->residentDataManager->getResidentsByNextPageLink($nextPageLink);

        return $residents;
    }

    /**
     * @param string $externalPropertyId
     * @return array
     */
    public function getResidents($externalPropertyId)
    {
        $this->residentDataManager->setSettings($this->settings);
        $residents = $this->residentDataManager->getResidents($externalPropertyId);

        return $residents;
    }
}
