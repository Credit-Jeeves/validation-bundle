<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

class StorageMRI extends ExternalApiStorage
{
    const IS_CURRENT = 'y';

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
        14 => Mapping::KEY_UNIT_ID,
        15 => Mapping::KEY_CITY,
        16 => Mapping::KEY_STREET,
        17 => Mapping::KEY_ZIP,
        18 => Mapping::KEY_STATE,
        19 => Mapping::KEY_EXTERNAL_PROPERTY_ID,
        20 => 'OnlyForCustomMapping-BuildingAddress',
        21 => 'OnlyForCustomMapping-Address'
    ];

    /**
     * @return bool
     */
    public function isMultipleProperty()
    {
        return true;
    }

    /**
     * @param  array $customers
     * @return bool
     */
    public function saveToFile(array $customers)
    {
        if (empty($customers)) {
            return false;
        }

        // API execution can be long, so restart the execution
        // timeout counter from zero and give us another 2 min (120 sec)
        set_time_limit(120);

        /** @var $customer Value  */
        foreach ($customers as $customer) {
            $filePath = $this->getFilePath(true);
            if (is_null($filePath)) {
                $this->initializeParameters();
            }

            if (strtolower($customer->getIsCurrent()) !== strtolower(self::IS_CURRENT)) {
                continue;
            }
            $leaseStart = $customer->getLeaseStart();
            if ($leaseStart) {
                $startAt = $this->getDateString($leaseStart);
            } else {
                $startAt = $this->getDateString($customer->getOccupyDateFormatted());
            }

            $address = $customer->getAddress() ? $customer->getAddress() : $customer->getBuildingAddress();

            $finishAt = $this->getDateString($customer->getLeaseEnd());
            $moveOut = $this->getDateString($customer->getLeaseMoveOut());
            $monthToMonth = $customer->getLeaseMonthToMonth();
            $isCurrent = $customer->getIsCurrent();
            $monthToMonth = strtolower($isCurrent) === 'y' ? $isCurrent : $monthToMonth;

            $externalUnitId = $customer->getExternalUnitId();
            $unitName = $customer->getUnitId();

            $data = [
                $customer->getResidentId(),
                $unitName,
                $startAt,
                $finishAt,
                $customer->getLeaseMonthlyRentAmount(),
                $customer->getFirstName(),
                $customer->getLastName(),
                $customer->getEmail(),
                $moveOut,
                $customer->getLeaseBalance(),
                $monthToMonth,
                $customer->getPaymentAccepted(),
                $customer->getLeaseId(),
                $externalUnitId,
                $customer->getCity(),
                $address,
                $customer->getZipCode(),
                $customer->getState(),
                $this->getImportExternalPropertyId(),
                $customer->getBuildingAddress(),
                $customer->getAddress()
            ];

            $this->writeCsvToFile($data);
        }

        return true;
    }
}
