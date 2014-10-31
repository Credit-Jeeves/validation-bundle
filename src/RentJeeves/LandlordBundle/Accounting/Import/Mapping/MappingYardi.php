<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageYardi;

class MappingYardi extends MappingCsv
{
    /**
     * @var ResidentDataClient
     */
    protected $residentData;

    public function __construct(
        StorageYardi $storage,
        CsvFileReaderImport $reader,
        ResidentDataManager $residentData
    ) {
        $this->residentData = $residentData;
        $this->storage = $storage;
        $this->reader = $reader;
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

    public function getResidents(Holding $holding, EntityProperty $property)
    {
        return $this->residentData->getCurrentResidents($holding, $property);
    }

    public function getContractData(Holding $holding, EntityProperty $property, $residentId)
    {
        return $this->residentData->getResidentData($holding, $property, $residentId);
    }
}
