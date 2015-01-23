<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use JMS\DiExtraBundle\Annotation\Service;
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

        /** @var $customer RtCustomer  */
        foreach ($cutomers as $customer) {
            $filePath = $this->getFilePath(true);
            if (is_null($filePath)) {
                $this->initializeParameters();
            }

            $startAt = $this->getDateString($customer->getCustomers()->getCustomer()->getLease()->getLeaseFromDate());
            $finishAt = $this->getDateString($customer->getCustomers()->getCustomer()->getLease()->getLeaseToDate());
            $moveOut = $this->getDateString($customer->getCustomers()->getCustomer()->getLease()->getActualMoveOut());

            $today = new \DateTime();

            if ($today > $finishAt) {
                $monthToMonth = 'Y';
            } else {
                $monthToMonth = 'N';
            }

            $data = array(
                $customer->getCustomerId(),
                $customer->getRtUnit()->getUnitId(),
                $startAt,
                $finishAt,
                $customer->getCustomers()->getCustomer()->getLease()->getCurrentRent(),
                $customer->getCustomers()->getCustomer()->getUserName()->getFirstName(),
                $customer->getCustomers()->getCustomer()->getUserName()->getLastName(),
                $customer->getCustomers()->getCustomer()->getAddress()->getEmail(),
                $moveOut,
                $this->getBalance($customer->getRtServiceTransactions()),
                $monthToMonth,
                $paymentAccepted = PaymentAccepted::ANY //when resman will show PaymentAccepted, must be use from resman
            );

            $this->writeCsvToFile($data);
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
            $credit = $transaction->getCredit();

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
