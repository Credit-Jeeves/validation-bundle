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
     * {@inheritdoc}
     */
    public function saveToFile($customers, $externalPropertyId = null)
    {
        if (!parent::saveToFile($customers)) {
            return false;
        }

        // API execution can be long, so restart the execution
        // timeout counter from zero and give us another 2 min (120 sec)
        set_time_limit(120);

        /** @var Value $customer   */
        foreach ($customers as $customer) {
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
                $this->getMonthToMonth($customer),
                $customer->getPaymentAccepted(),
                $customer->getLeaseId(),
                $externalUnitId,
                $customer->getCity(),
                $address,
                $customer->getZipCode(),
                $customer->getState(),
                $externalPropertyId,
                $customer->getBuildingAddress(),
                $customer->getAddress()
            ];

            $this->writeCsvToFile($data);
        }

        return true;
    }


    /**
     * @param Value $customer
     *
     * @return string "Y" or whatever API sends us
     */
    protected function getMonthToMonth(Value $customer)
    {
        $monthToMonth = $customer->getLeaseMonthToMonth();
        /** @var \DateTime $leaseEnd */
        $leaseEnd = $customer->getLeaseEnd();
        if ($leaseEnd instanceof \DateTime) {
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            $leaseEnd->setTime(0, 0, 0);
            $isPastFinishDate = $leaseEnd < $today;
        } else {
            $isPastFinishDate = false;
        }

        $isCurrent = strtolower($customer->getIsCurrent()) === 'y' ? true : false;
        // if past finishedAt and Current, then force month-to-month
        if ($isPastFinishDate && $isCurrent) {
            $monthToMonth = 'Y';
        }

        return $monthToMonth;
    }
}
