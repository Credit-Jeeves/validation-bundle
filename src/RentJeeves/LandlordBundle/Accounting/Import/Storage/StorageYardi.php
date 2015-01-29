<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
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
    public function saveToFile(ResidentLeaseFile $residentData, $residentId, $moveOutDate, $paymentAccepted)
    {
        $filePath = $this->getFilePath(true);
        if (is_null($filePath)) {
            $this->initializeParameters();
        }

        $today = new DateTime();
        $leaseEnd = DateTime::createFromFormat('Y-m-d', $residentData->getLeaseEnd());
        $monthToMonth = ($today > $leaseEnd)? 'Y' : 'N';
        $ledgerDetails = $residentData->getLedgerDetails();
        if (empty($ledgerDetails)) {
            throw new ImportStorageException("Don't have permission for getting balance.");
        }

        $data = array(
            $residentId,
            $residentData->getUnit()->getIdentification()->getUnitName(),
            $residentData->getLeaseBegin(),
            $residentData->getLeaseEnd(),
            $residentData->getMonthlyRentAmount(),
            $residentData->getTenantDetails()->getPersonDetails()->getName()->getFirstName(),
            $residentData->getTenantDetails()->getPersonDetails()->getName()->getLastName(),
            $residentData->getTenantDetails()->getPersonDetails()->getEmail(),
            $moveOutDate,
            $ledgerDetails->getIdentification()->getBalance(),
            $monthToMonth,
            $paymentAccepted
        );

        $this->writeCsvToFile($data);
    }
}
