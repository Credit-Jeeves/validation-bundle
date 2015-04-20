<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

/**
 * @Service("accounting.import.storage.amsi")
 */
class StorageAMSI extends ExternalApiStorage
{
    /**
     * @return bool
     */
    public function isMultipleProperty()
    {
        return true;
    }

    /**
     * @{inheritdoc}
     */
    protected function initializeParameters()
    {
        $this->setFieldDelimiter(self::FIELD_DELIMITER);
        $this->setTextDelimiter(self::TEXT_DELIMITER);
        $this->setDateFormat(self::DATE_FORMAT);

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
            13 => Mapping::KEY_EXTERNAL_LEASE_ID,
            14 => Mapping::KEY_UNIT_ID,
            15 => Mapping::KEY_CITY,
            16 => Mapping::KEY_STREET,
            17 => Mapping::KEY_ZIP,
            18 => Mapping::KEY_STATE
        ];

        $this->writeCsvToFile($mapping);
        $this->setMapping($mapping);
    }

    /**
     * @param  array   $residentLeases
     * @return boolean
     */
    public function saveToFile(array $residentLeases)
    {
        if (count($residentLeases) <= 0) {
            return false;
        }

        $filePath = $this->getFilePath(true);
        if (is_null($filePath)) {
            $this->initializeParameters();
        }

        /** @var Lease $lease */
        foreach ($residentLeases as $lease) {
            $paymentAccepted = $lease->getBlockPaymentAccess();
            if (strtolower($paymentAccepted) === 'y') {
                $paymentAccepted = PaymentAccepted::ANY;
            } else {
                $paymentAccepted = PaymentAccepted::DO_NOT_ACCEPT;
            }

            //@TODO we don't have it in response and don't know format this field,
            // don't use it, wait response from customer
            $moveOutDate = $lease->getActualMoveOutDate();

            $balance = $lease->getEndBalance();
            $rent = $lease->getRentAmount();
            $startAt = $lease->getLeaseBeginDateObject();
            $finishAt = $lease->getLeaseEndDateObject();
            $today = new \DateTime();
            if ($finishAt instanceof \DateTime && $today > $finishAt) {
                $monthToMonth = 'Y';
            } else {
                $monthToMonth = 'N';
            }

            $unit = $lease->getUnit();

            $street = $unit->getAddress1();
            $city = $unit->getCity();
            $country = $unit->getCountry();
            $zip = $unit->getZip();
            $state = $unit->getState();

            $occupants = $lease->getOccupants();
            /** @var Occupant $occupant */
            foreach ($occupants as $occupant) {
                $firstName = $occupant->getOccuFirstName();
                $lastName = $occupant->getOccuLastName();
                $email = $occupant->getEmail();
                $unitName = $occupant->getUnitId();
                $residentId = $occupant->getOccuSeqNo();
                $externalUnitId = sprintf(
                    '%s|%s|%s',
                    $lease->getPropertyId(),
                    $lease->getBldgId(),
                    $lease->getUnitId()
                );

                $data = [
                    $residentId,
                    $unitName,
                    $this->getDateString($startAt),
                    $this->getDateString($finishAt),
                    $rent,
                    $firstName,
                    $lastName,
                    $email,
                    $moveOut = null, // see todo
                    $balance,
                    $monthToMonth,
                    $paymentAccepted,
                    $lease->getResiId(),
                    $externalUnitId,
                    $city,
                    $street,
                    $zip,
                    $state
                ];

                $this->writeCsvToFile($data);
            }
        }

        return true;
    }
}
