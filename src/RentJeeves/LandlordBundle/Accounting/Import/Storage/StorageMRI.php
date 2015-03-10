<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;

/**
 * @Service("accounting.import.storage.mri")
 */
class StorageMRI extends ExternalApiStorage
{
    /**
     * @param array $customers
     * @return bool
     */
    public function saveToFile(array $customers)
    {
        if (empty($customers)) {
            return false;
        }

        ini_set('max_execution_time', '120');

        /** @var $customer Value  */
        foreach ($customers as $customer) {
            $filePath = $this->getFilePath(true);
            if (is_null($filePath)) {
                $this->initializeParameters();
            }


            $startAt = $this->getDateString($customer->getLeaseStart());
            $finishAt = $this->getDateString($customer->getLeaseEnd());
            $moveOut = $this->getDateString($customer->getLeaseMoveOut());
            $paymentAccepted = strtolower($customer->getPayAllowed()); //@TODO need find out possible values

            $data = array(
                $customer->getResidentId(),
                $customer->getUnitId(),
                $startAt,
                $finishAt,
                $customer->getLeaseMonthlyRentAmount(),
                $customer->getFirstName(),
                $customer->getLastName(),
                $customer->getEmail(),
                $moveOut,
                $customer->getLeaseBalance(),
                $customer->getLeaseMonthToMonth(),
                $paymentAccepted,
                $customer->getLeaseId()
            );

            $this->writeCsvToFile($data);
        }

        return true;
    }
}
