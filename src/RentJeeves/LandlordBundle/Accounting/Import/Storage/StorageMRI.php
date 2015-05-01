<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

/**
 * @Service("accounting.import.storage.mri")
 */
class StorageMRI extends ExternalApiStorage
{
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
            18 => Mapping::KEY_STATE,
            19 => Mapping::KEY_EXTERNAL_PROPERTY_ID
        ];

        $this->writeCsvToFile($mapping);
        $this->setMapping($mapping);
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
        //API execution can be long
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

            $data = [
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
                $this->getPayAllowed($customer),
                $customer->getLeaseId(),
                $customer->getUnitId(),
                $customer->getCity(),
                $customer->getAddress(),
                $customer->getZipCode(),
                $customer->getState(),
                $this->getImportExternalPropertyId()
            ];

            $this->writeCsvToFile($data);
        }

        return true;
    }

    /**
     * @param  Value $customer
     * @return int
     */
    protected function getPayAllowed(Value $customer)
    {
        $payAllowed = trim(strtolower($customer->getPayAllowed()));

        if ($payAllowed === 'd') {
            return PaymentAccepted::DO_NOT_ACCEPT;
        }

        if (empty($payAllowed) || $payAllowed === 'c') {
            return PaymentAccepted::ANY;
        }
    }
}
