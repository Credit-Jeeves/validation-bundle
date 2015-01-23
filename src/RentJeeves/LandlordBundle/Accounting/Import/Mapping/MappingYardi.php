<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageYardi;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;

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
        $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
            array('holding' => $holding->getId(), 'property'=> $property->getId())
        );

        if (empty($propertyMapping)) {
            throw new ImportMappingException(
                sprintf(
                    "Don't have external property id for property: %s and holding: %s",
                    $property->getId(),
                    $holding->getId()
                )
            );
        }

        $externalPropertyId = $propertyMapping->getExternalPropertyId();
        $transactionData = $this->residentData->getResidentTransactions($holding, $externalPropertyId);
        $residentsTransaction = $transactionData->getProperty()->getCustomers();

        $residents = $this->residentData->getCurrentResidents($holding, $property);

        /**
         * @var $resident ResidentsResident
         */
        foreach ($residents as $key => $resident) {
            $tCode = $resident->getCode();
            /**
             * @var $residentTransaction ResidentTransactionPropertyCustomer
             */
            foreach ($residentsTransaction as $residentTransaction) {
                if ($residentTransaction->getCustomerId() === $tCode) {
                    $residents[$key]->setPaymentAccepted($residentTransaction->getPaymentAccepted());
                    continue;
                }
            }
        }

        return $residents;
    }

    public function getContractData(Holding $holding, EntityProperty $property, $residentId)
    {
        return $this->residentData->getResidentData($holding, $property, $residentId);
    }
}
