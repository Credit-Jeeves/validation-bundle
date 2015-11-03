<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingYardi as Mapping;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;

class StorageYardi extends ExternalApiStorage
{
    /**
     * @param ResidentLeaseFile $residentData
     * @param ResidentsResident $resident
     */
    public function saveToFile(ResidentLeaseFile $residentData, ResidentsResident $resident)
    {
        $filePath = $this->getFilePath(true);
        if (is_null($filePath)) {
            $this->initializeParameters();
        }

        if ($resident->isRoommate()) {
            return $this->saveToFileRoommate($residentData, $resident);
        }

        return $this->saveToFileCustomer($residentData, $resident);
    }

    /**
     * Create and write header into CSV file
     *
     * @throws ImportStorageException
     */
    protected function initializeParameters()
    {
        $this->setFieldDelimiter(self::FIELD_DELIMITER);
        $this->setTextDelimiter(self::TEXT_DELIMITER);
        $this->setDateFormat(self::DATE_FORMAT);
        $this->setPropertyId($this->getImportPropertyId());

        if (!$mapping = $this->getMappingFromDB()) {
            $mapping = [
                1 => Mapping::KEY_RESIDENT_ID,
                2 => Mapping::KEY_UNIT,
                3 => Mapping::KEY_MOVE_IN,
                4 => Mapping::KEY_LEASE_END,
                5 => Mapping::KEY_RENT,
                6 => Mapping::FIRST_NAME_TENANT,
                7 => Mapping::LAST_NAME_TENANT,
                8 => Mapping::KEY_EMAIL,
                9 => Mapping::KEY_MOVE_OUT,
                10 => Mapping::KEY_BALANCE,
                11 => Mapping::KEY_MONTH_TO_MONTH,
                12 => Mapping::KEY_PAYMENT_ACCEPTED,
                13 => Mapping::KEY_EXTERNAL_LEASE_ID
            ];
        }

        if ($this->isMultiplePropertyMapping()) {
            $mapping[14] = Mapping::KEY_PROPERTY_ID;
        }

        $this->writeCsvToFile($mapping);
        $this->setMapping($mapping);
    }

    /**
     * @param ResidentLeaseFile $residentData
     * @param ResidentsResident $resident
     * @throws ImportStorageException
     */
    protected function saveToFileRoommate(ResidentLeaseFile $residentData, ResidentsResident $resident)
    {
        $residentId = $resident->getResidentId();
        $moveOutDate = $resident->getMoveOutDate(true);
        $paymentAccepted = $resident->getPaymentAccepted();
        $leaseId = $resident->getLeaseId();
        $format = $this->getDateFormat();

        $ledgerDetails = $this->getLedgerDetails($residentData);
        if ($resident->getMoveInDate()) {
            $startAt = $resident->getMoveInDate(true)->format($format);
        } else {
            $startAt = $residentData->getLeaseBegin();
        }
        $finishAt = $moveOutDate ? $moveOutDate->format($format) : $residentData->getLeaseEnd();

        $today = new \DateTime();
        $leaseEnd = $residentData->getLeaseEnd(true);
        $monthToMonth = ($today > $leaseEnd && empty($moveOutDate)) ? 'Y' : 'N';
        $data = [

            $residentId,
            $residentData->getUnit()->getIdentification()->getUnitName(),
            $startAt,
            $finishAt,
            $residentData->getMonthlyRentAmount(),
            $resident->getFirstName(),
            $resident->getLastName(),
            $resident->getEmail(),
            ($moveOutDate instanceof \DateTime) ? $moveOutDate->format($format) : '',
            $ledgerDetails->getIdentification()->getBalance(),
            $monthToMonth,
            $paymentAccepted,
            $leaseId
        ];

        if ($this->isMultiplePropertyMapping()) {
            $data[] = $this->getImportPropertyId();
        }

        $this->writeCsvToFile($data);
    }

    /**
     * @param ResidentLeaseFile $residentData
     * @param ResidentsResident $resident
     * @throws ImportStorageException
     */
    protected function saveToFileCustomer(ResidentLeaseFile $residentData, ResidentsResident $resident)
    {
        $residentId = $resident->getResidentId();
        $moveOutDate = $resident->getMoveOutDate(true);
        $moveOutDate = $moveOutDate instanceof DateTime ? $moveOutDate->format($this->getDateFormat()) : '';
        $paymentAccepted = $resident->getPaymentAccepted();
        $leaseId = $resident->getLeaseId();

        $today = new \DateTime();
        $leaseEnd = $residentData->getLeaseEnd(true);
        $monthToMonth = ($today > $leaseEnd && empty($moveOutDate)) ? 'Y' : 'N';
        $ledgerDetails = $this->getLedgerDetails($residentData);

        $data = [
            $residentId,
            $residentData->getUnit()->getIdentification()->getUnitName(),
            $residentData->getLeaseBegin(),
            $residentData->getLeaseEnd(),
            $residentData->getMonthlyRentAmount(),
            $residentData->getTenantDetails()->getPersonDetails()->getName()->getFirstName(),
            $residentData->getTenantDetails()->getPersonDetails()->getName()->getLastName(),
            $residentData->getTenantDetails()->getPersonDetails()->getEmail(),
            ($moveOutDate instanceof \DateTime) ? $moveOutDate->format($this->getDateFormat()) : '',
            $ledgerDetails->getIdentification()->getBalance(),
            $monthToMonth,
            $paymentAccepted,
            $leaseId
        ];

        if ($this->isMultiplePropertyMapping()) {
            $data[] = $this->getImportPropertyId();
        }

        $this->writeCsvToFile($data);
    }

    /**
     * @param ResidentLeaseFile $residentData
     * @return null|\RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileLedger
     * @throws ImportStorageException
     */
    protected function getLedgerDetails(ResidentLeaseFile $residentData)
    {
        $ledgerDetails = $residentData->getLedgerDetails();

        if (empty($ledgerDetails)) {
            throw new ImportStorageException("Don't have permission for getting balance.");
        }

        return $ledgerDetails;
    }

    /**
     * @return bool
     */
    public function isMultipleProperty()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isMultiplePropertyMapping()
    {
        return !!$this->session->get(self::IS_MULTIPLE_PROPERTY, false);
    }
}
