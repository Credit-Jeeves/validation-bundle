<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtServiceTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transactions;
use Symfony\Component\HttpFoundation\Session\Session;
use RentJeeves\DataBundle\Enum\PaymentAccepted;

/**
 * @Service("accounting.import.storage.resman")
 */
class StorageResman extends ExternalApiStorage
{
    /**
     * @param array $cutomers
     * @return bool
     */
    public function saveToFile(array $cutomers)
    {
        if (empty($cutomers)) {
            return false;
        }

        ini_set('max_execution_time', '120');

        /** @var $customerBase RtCustomer  */
        foreach ($cutomers as $customerBase) {
            $filePath = $this->getFilePath(true);
            if (is_null($filePath)) {
                $this->initializeParameters();
            }

            if ($customerBase->getCustomers()->getCustomer()->count() === 0) {
                continue;
            }

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

                if ($today > $finishAt) {
                    $monthToMonth = 'Y';
                } else {
                    $monthToMonth = 'N';
                }

                $data = array(
                    $customerUser->getCustomerId(),
                    $customerBase->getRtUnit()->getUnitId(),
                    $startAt,
                    $finishAt,
                    $customerUser->getLease()->getCurrentRent(),
                    $customerUser->getUserName()->getFirstName(),
                    $customerUser->getUserName()->getLastName(),
                    $customerUser->getAddress()->getEmail(),
                    $moveOut,
                    $this->getBalance($customerBase->getRtServiceTransactions()),
                    $monthToMonth,
                    $paymentAccepted
                );

                $this->writeCsvToFile($data);
            }
        }

        return true;
    }

    /**
     * @param RtServiceTransactions $rtServiceTransactions
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
     * @param string|\DateTime $date
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
