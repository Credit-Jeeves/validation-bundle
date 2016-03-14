<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\OtherOccupant;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\OtherOccupants;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
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

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @return array
     */
    public function getResidents(Holding $holding, $externalPropertyId)
    {
        $this->residentData->setSettings($holding->getYardiSettings());
        $residentsTransaction = $this->residentData->getResidentTransactions($externalPropertyId);
        $residents = $this->residentData->getCurrentAndNoticesResidents($externalPropertyId);

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

    /**
     * @param Holding $holding
     * @param ResidentsResident $resident
     * @param string $externalPropertyId
     * @return ResidentLeaseFile
     * @throws \Exception
     */
    public function getContractData(Holding $holding, ResidentsResident $resident, $externalPropertyId)
    {
        if ($resident->isRoommate()) {
            $residentId = $resident->getLeaseId();
        } else {
            $residentId = $resident->getResidentId();
        }

        $this->residentData->setSettings($holding->getExternalSettings());

        return $this->residentData->getResidentData($residentId, $externalPropertyId);
    }
}
