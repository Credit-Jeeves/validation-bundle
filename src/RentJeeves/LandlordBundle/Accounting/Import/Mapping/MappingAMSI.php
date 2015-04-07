<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\DataBundle\Entity\AMSISettings;
use RentJeeves\ExternalApiBundle\Services\AMSI\ResidentDataManager;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAMSI;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;
use Symfony\Component\Security\Core\SecurityContext;

class MappingAMSI extends MappingCsv
{
    /**
     * @var ResidentDataManager $residentDataManager
     */
    protected $residentDataManager;

    /**
     * @var AMSISettings $settings
     */
    protected $settings;

    public function __construct(
        StorageAMSI $storage,
        CsvFileReaderImport $reader,
        ResidentDataManager $residentDataManager,
        SecurityContext $securityContext
    ) {
        $this->storage = $storage;
        $this->reader = $reader;
        $this->residentDataManager = $residentDataManager;
        /** @var Holding $holding */
        $holding = $securityContext->getToken()->getUser()->getHolding();

        if (empty($holding)) {
            throw new ImportMappingException(
                sprintf(
                    "User(id:%s) doesn't have holding",
                    $securityContext->getToken()->getUser()->getId()
                )
            );
        }

        $this->settings = $holding->getAmsiSettings();

        if (empty($this->settings)) {
            throw new ImportMappingException(
                sprintf(
                    "Holding(id:%s) doesn't have AMSI Settings",
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
