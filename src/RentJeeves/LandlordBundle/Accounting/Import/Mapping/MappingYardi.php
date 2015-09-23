<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\OtherOccupant;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\OtherOccupants;
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

        $residents = $this->residentData->getCurrentAndNoticesResidents($holding, $property);
        $roommates = [];
        /** @var $resident ResidentsResident */
        foreach ($residents as $key => $resident) {
            //Leas id the same as resident ID for general tenant
            $leaseId = $resident->getCode();
            /** @var ResidentTransactionPropertyCustomer $residentTransaction  */
            foreach ($residentsTransaction as $residentTransaction) {
                if ($residentTransaction->getLeaseId() !== $leaseId) {
                    continue;
                }

                $residents[$key]->setPaymentAccepted($residentTransaction->getPaymentAccepted());
                $residents[$key]->setLeaseId($leaseId);
                //Process roommates
                /** @var OtherOccupants $otherOccupants */
                $otherOccupants = $resident->getOtherOccupants();
                if (empty($otherOccupants)) {
                    continue;
                }

                $otherOccupantArray = $otherOccupants->getOtherOccupants();
                /** @var OtherOccupant $otherOccupant */
                foreach ($otherOccupantArray as $otherOccupant) {
                    $resident = new ResidentsResident();
                    $resident->setCode($otherOccupant->getResidentId());
                    $resident->setPaymentAccepted($residentTransaction->getPaymentAccepted());
                    $resident->setLeaseId($leaseId);
                    $resident->setFirstName($otherOccupant->getFirstName());
                    $resident->setLastName($otherOccupant->getLastName());
                    $resident->setEmail($otherOccupant->getEmail());
                    $resident->setIsRoommate(true);
                    $resident->setMoveOutDate($otherOccupant->getMoveOutDate());
                    $resident->setMoveInDate($otherOccupant->getMoveInDate());
                    $roommates[] = $resident;
                }
                // Don't need return this data to client,
                // but serializer don't have option for disable only desiralization
                // That's why need set to null
                $residents[$key]->setOtherOccupants(null);

            }
        }

        return array_merge($residents, $roommates);
    }

    public function getContractData(Holding $holding, EntityProperty $property, ResidentsResident $resident)
    {
        if ($resident->isRoommate()) {
            $residentId = $resident->getLeaseId();
        } else {
            $residentId = $resident->getResidentId();
        }

        return $this->residentData->getResidentData($holding, $property, $residentId);
    }
}
