<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Tenant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

class StorageResman extends ExternalApiStorage
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
        14 => Mapping::KEY_UNIT_ID,
        15 => Mapping::KEY_CITY,
        16 => Mapping::KEY_STREET,
        17 => Mapping::KEY_ZIP,
        18 => Mapping::KEY_STATE,
        19 => Mapping::KEY_EXTERNAL_PROPERTY_ID,
        20 => Mapping::KEY_TENANT_STATUS
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
    public function saveToFile($customers)
    {
        if (!parent::saveToFile($customers)) {
            return false;
        }

        ini_set('max_execution_time', '120');
        /** @var $customerBase RtCustomer  */
        foreach ($customers as $customerBase) {
            if (count($customerBase->getCustomers()->getCustomer()) === 0) {
                continue;
            }

            $externalLeaseId = $customerBase->getCustomerId();
            /** @var Customer $customerUser */
            foreach ($customerBase->getCustomers()->getCustomer() as $customerUser) {
                $type = $customerUser->getType();
                if ($type !== 'current resident') {
                    continue;
                }

                $startAt = $this->getDateString($customerUser->getLease()->getLeaseFromDate());
                $finishAt = $this->getDateString($customerUser->getLease()->getLeaseToDate());
                $moveOut = $this->getDateString($customerUser->getLease()->getActualMoveOut());
                $paymentAccepted = $customerBase->getRentTrackPaymentAccepted();
                $today = new \DateTime();
                $finishAtObject = \DateTime::createFromFormat('Y-m-d', $finishAt);

                if ($today > $finishAtObject && empty($moveOut)) {
                    $monthToMonth = 'Y';
                } else {
                    $monthToMonth = 'N';
                }

                $residentId = $customerUser->getCustomerId();
                $address = $customerUser->getAddress();
                $externalUnitId = $customerUser->getExternalUnitId($customerBase);
                $data = [
                    $residentId,
                    $customerBase->getRtUnit()->getUnitId(),
                    $startAt,
                    $finishAt,
                    $customerUser->getLease()->getCurrentRent(),
                    $customerUser->getUserName()->getFirstName(),
                    $customerUser->getUserName()->getLastName(),
                    $address->getEmail(),
                    $moveOut,
                    $customerBase->getRentTrackBalance(),
                    $monthToMonth,
                    $paymentAccepted,
                    $externalLeaseId,
                    $externalUnitId,
                    $address->getCity(),
                    $address->getAddress1(),
                    $address->getPostalCode(),
                    $address->getState(),
                    $this->getImportExternalPropertyId(),
                    Tenant::$tenantStatusCurrent
                ];

                $this->writeCsvToFile($data);
            }
        }

        return true;
    }

    /**
     * @param  string|\DateTime $date
     * @return string
     */
    protected function getDateString($date)
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }

        return $date;
    }
}
