<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;
use Symfony\Component\HttpFoundation\Session\Session;
use DateTime;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.storage.yardi")
 */
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
        if ($resident->getMoveInDate()){
            $startAt = $resident->getMoveInDate(true)->format($format);
        } else {
            $startAt = $residentData->getLeaseBegin();
        }
        $finishAt = $moveOutDate ? $moveOutDate->format($format) : $residentData->getLeaseEnd();

        $today = new DateTime();
        $leaseEnd = $residentData->getLeaseEnd();
        $monthToMonth = ($today > $leaseEnd)? 'Y' : 'N';

        $data = array(
            $residentId,
            $residentData->getUnit()->getIdentification()->getUnitName(),
            $startAt,
            $finishAt,
            $residentData->getMonthlyRentAmount(),
            $resident->getFirstName(),
            $resident->getLastName(),
            $resident->getEmail(),
            ($moveOutDate instanceof DateTime)? $moveOutDate->format($format) : '',
            $ledgerDetails->getIdentification()->getBalance(),
            $monthToMonth,
            $paymentAccepted,
            $leaseId
        );

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
        $paymentAccepted = $resident->getPaymentAccepted();
        $leaseId = $resident->getLeaseId();

        $today = new DateTime();
        $leaseEnd = $residentData->getLeaseEnd(true);
        $monthToMonth = ($today > $leaseEnd)? 'Y' : 'N';
        $ledgerDetails = $this->getLedgerDetails($residentData);

        $data = array(
            $residentId,
            $residentData->getUnit()->getIdentification()->getUnitName(),
            $residentData->getLeaseBegin(),
            $residentData->getLeaseEnd(),
            $residentData->getMonthlyRentAmount(),
            $residentData->getTenantDetails()->getPersonDetails()->getName()->getFirstName(),
            $residentData->getTenantDetails()->getPersonDetails()->getName()->getLastName(),
            $residentData->getTenantDetails()->getPersonDetails()->getEmail(),
            ($moveOutDate instanceof DateTime)? $moveOutDate->format($this->getDateFormat()) : '',
            $ledgerDetails->getIdentification()->getBalance(),
            $monthToMonth,
            $paymentAccepted,
            $leaseId
        );

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
}
