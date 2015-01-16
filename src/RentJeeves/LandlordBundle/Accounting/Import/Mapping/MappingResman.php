<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResidentDataManager;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageResman;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;

class MappingResman extends MappingCsv
{
    /**
     * @var $residentDataManager ResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var $settings ResManSettings
     */
    protected $settings;

    public function __construct(
        StorageResman $storage,
        CsvFileReaderImport $reader,
        ResidentDataManager $residentDataManager,
        $securityContext
    ) {
        $this->storage = $storage;
        $this->reader = $reader;
        $this->residentDataManager = $residentDataManager;
        $this->settings = $securityContext->getToken()->getUser()->getHolding()->getResManSettings();

        if (empty($this->settings)) {
            throw new ImportMappingException(
                sprintf(
                    "Holding(id:%s) don't have ResMan Settings",
                    $securityContext->getToken()->getUser()->getHolding()->getId()
                )
            );
        }
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
     * @param $externalPropertyId
     * @return array
     */
    public function getResidents($externalPropertyId)
    {
        $this->residentDataManager->setSettings($this->settings);

        return $this->residentDataManager->getResidents($externalPropertyId);
    }
}
