<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use RentJeeves\ExternalApiBundle\Model\AMSI\RecurringCharge;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

class StorageAMSI extends ExternalApiStorage
{
    /**
     * @var array
     */
    protected $defaultMapping = [
        1 => Mapping::KEY_UNIT,
        2 => Mapping::KEY_MOVE_IN,
        3 => Mapping::KEY_LEASE_END,
        4 => Mapping::KEY_RENT,
        5 => Mapping::FIRST_NAME_TENANT,
        6 => Mapping::LAST_NAME_TENANT,
        7 => Mapping::KEY_EMAIL,
        8 => Mapping::KEY_MOVE_OUT,
        9 => Mapping::KEY_BALANCE,
        10 => Mapping::KEY_MONTH_TO_MONTH,
        11 => Mapping::KEY_PAYMENT_ACCEPTED,
        12 => Mapping::KEY_EXTERNAL_LEASE_ID,
        13 => Mapping::KEY_UNIT_ID,
        14 => Mapping::KEY_CITY,
        15 => Mapping::KEY_STREET,
        16 => Mapping::KEY_ZIP,
        17 => Mapping::KEY_STATE,
        18 => Mapping::KEY_EXTERNAL_PROPERTY_ID
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
    public function saveToFile($residentLeases, $externalPropertyId = null)
    {
        if (!parent::saveToFile($residentLeases)) {
            return false;
        }

        /** @var Lease $lease */
        foreach ($residentLeases as $lease) {
            $unit = $lease->getUnit();
            if ($unit === null) {
                continue;  // we don't have unit details -- skip.
            }

            $paymentAccepted = $lease->getBlockPaymentAccess();
            if (strtolower($paymentAccepted) === 'y') {
                $paymentAccepted = PaymentAccepted::DO_NOT_ACCEPT;
            } else {
                $paymentAccepted = PaymentAccepted::ANY;
            }

            $moveOutDate = $lease->getActualMoveOutDateObject();
            $balance = $lease->getEndBalance();
            $rent = $this->getLeaseRent($lease);
            $startAt = $lease->getLeaseBeginDateObject();
            $finishAt = $lease->getLeaseEndDateObject();
            $today = new \DateTime();
            if ($finishAt instanceof \DateTime && $today > $finishAt && empty($moveOutDate)) {
                $monthToMonth = 'Y';
            } else {
                $monthToMonth = 'N';
            }

            $street = $unit->getAddress1();
            $city = $unit->getCity();
            $zip = $unit->getZip();
            $state = $unit->getState();

            $occupants = $lease->getOccupants();
            /** @var Occupant $occupant */
            foreach ($occupants as $occupant) {
                $firstName = $occupant->getOccuFirstName();
                $lastName = $occupant->getOccuLastName();
                $email = $occupant->getEmail();
                $unitName = $occupant->getUnitId();
                $externalUnitId = $lease->getExternalUnitId();

                $data = [
                    $unitName,
                    $this->getDateString($startAt),
                    $this->getDateString($finishAt),
                    $rent,
                    $firstName,
                    $lastName,
                    $email,
                    $this->getDateString($moveOutDate),
                    $balance,
                    $monthToMonth,
                    $paymentAccepted,
                    $lease->getResiId(),
                    $externalUnitId,
                    $city,
                    $street,
                    $zip,
                    $state,
                    $externalPropertyId
                ];

                $this->writeCsvToFile($data);
            }
        }

        return true;
    }

    /**
     * @param Lease $lease
     * @return float
     */
    protected function getLeaseRent(Lease $lease)
    {
        $rent = $lease->getRentAmount();
        if ($rent == 0) {
            /** @var RecurringCharge $recurringCharge */
            foreach ($lease->getRecurringCharges() as $recurringCharge) {
                if (RecurringCharge::RENT_INCOME_CODE_ID == $recurringCharge->getIncCode() &&
                    RecurringCharge::FREQUENCY_MONTH == $recurringCharge->getFreqCode() &&
                    Lease::STATUS_CURRENT == $lease->getOccuStatusCode()
                ) {
                    return $recurringCharge->getAmount();
                }
            }
        }

        return $rent;
    }
}
