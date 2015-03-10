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

    public function __construct(
        StorageMRI $storage,
        CsvFileReaderImport $reader,
        ResidentDataManager $residentDataManager,
        SecurityContext $securityContext
    ) {
        $this->storage = $storage;
        $this->reader = $reader;
        $this->residentDataManager = $residentDataManager;
        $this->settings = $securityContext->getToken()->getUser()->getHolding()->getMriSettings();

        if (empty($this->settings)) {
            throw new ImportMappingException(
                sprintf(
                    "Holding(id:%s) don't have MRI Settings",
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
