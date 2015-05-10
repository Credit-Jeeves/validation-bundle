<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtServiceTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transactions;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

/**
 * @Service("accounting.import.storage.resman")
 */
class StorageResman extends ExternalApiStorage
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

        ini_set('max_execution_time', '120');

        /** @var $customerBase RtCustomer  */
        foreach ($customers as $customerBase) {
            $filePath = $this->getFilePath(true);
            if (is_null($filePath)) {
                $this->initializeParameters();
            }

            if ($customerBase->getCustomers()->getCustomer()->count() === 0) {
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
                $paymentAccepted = strtolower($customerBase->getPaymentAccepted());
                /**
                 * Possible Values are:
                 * Yes - All forms of payment accepted
                 * No - Online payments are not accepted
                 * Certified Funds Only - Only payments guaranteed to be successful are allowed
                 * (Credit Card, Debit Card, Money Order, etc)
                 *
                 * Currently we don't work with 3 point - it's will be seperated task
                 */
                $paymentAccepted = ('yes' === $paymentAccepted) ? PaymentAccepted::ANY : PaymentAccepted::DO_NOT_ACCEPT;
                $today = new \DateTime();
                $finishAtObject = \DateTime::createFromFormat('Y-m-d', $finishAt);

                if ($today > $finishAtObject) {
                    $monthToMonth = 'Y';
                } else {
                    $monthToMonth = 'N';
                }

                $residentId = $customerUser->getCustomerId();
                $address = $customerUser->getAddress();

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
                    $this->getBalance($customerBase->getRtServiceTransactions()),
                    $monthToMonth,
                    $paymentAccepted,
                    $externalLeaseId,
                    $customerBase->getRtUnit()->getUnitId(),
                    $address->getCity(),
                    $address->getAddress1(),
                    $address->getPostalCode(),
                    $address->getState(),
                    $this->getImportExternalPropertyId()
                ];

                $this->writeCsvToFile($data);
            }
        }

        return true;
    }

    /**
     * @param  RtServiceTransactions $rtServiceTransactions
     * @return float|int
     */
    protected function getBalance(RtServiceTransactions $rtServiceTransactions)
    {
        $transactions = $rtServiceTransactions->getTransactions();
        $balance = 0;

        /**
         * @var $transaction Transactions
         */
        foreach ($transactions as $transaction) {

            $charge = $transaction->getCharge();
            $payment = $transaction->getPayment();
            $credit = $transaction->getConcession();

            if (!empty($charge)) {
                $balance += $charge->getDetail()->getAmount();
                continue;
            }

            if (!empty($payment)) {
                $balance -= $payment->getDetail()->getAmount();
                continue;
            }

            if (!empty($credit)) {
                $balance -= $credit->getDetail()->getAmount();
            }
        }

        return $balance;
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
