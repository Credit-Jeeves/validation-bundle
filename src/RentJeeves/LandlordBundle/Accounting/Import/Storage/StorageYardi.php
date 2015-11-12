<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingYardi as Mapping;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;

class StorageYardi extends ExternalApiStorage
{
    /**
     * @var array
     */
    protected $defaultMapping = [
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
        13 => Mapping::KEY_EXTERNAL_LEASE_ID,
        14 => Mapping::KEY_EXTERNAL_PROPERTY_ID,
        15 => Mapping::KEY_CITY,
        16 => Mapping::KEY_STATE,
        17 => Mapping::KEY_ZIP,
        18 => Mapping::KEY_STREET,
        19 => 'Not used',
        20 => 'Not used2',
    ];

    /**
     * {@inheritdoc}
     */
    public function saveToFile($fullResident)
    {
        if (!parent::saveToFile($fullResident)) {
            return false;
        }
        $resident = $fullResident->getResident();
        $residentData = $fullResident->getResidentData();

        if ($resident->isRoommate()) {
            return $this->saveToFileRoommate($residentData, $resident);
        }

        return $this->saveToFileCustomer($residentData, $resident);
    }

    /**
     * @param ResidentLeaseFile $residentData
     * @param ResidentsResident $resident
     * @throws ImportStorageException
     */
    protected function saveToFileRoommate(
        ResidentLeaseFile $residentData,
        ResidentsResident $resident,
        Property $property
    ) {
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
            $leaseId,
            $property->getCode(),
            $property->getCity(),
            $property->getState(),
            $property->getPostalCode(),
            $property->getAddressLine1(),
            $property->getAddressLine2(),
            $property->getAddressLine3(),

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
    protected function saveToFileCustomer(ResidentLeaseFile $residentData, ResidentsResident $resident, Property $property)
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
            $leaseId,
            $property->getCode(),
            $property->getCity(),
            $property->getState(),
            $property->getPostalCode(),
            $property->getAddressLine1(),
            $property->getAddressLine2(),
            $property->getAddressLine3(),
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
        return true;
    }

    /**
     * @return bool
     */
    public function isMultiplePropertyMapping()
    {
        return !!$this->session->get(self::IS_MULTIPLE_PROPERTY, false);
    }
}
