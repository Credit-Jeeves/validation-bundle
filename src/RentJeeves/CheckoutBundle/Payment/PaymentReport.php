<?php

namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;
use JMS\DiExtraBundle\Annotation as DI;
use DateTime;

/**
 * @DI\Service("payment.report")
 */
class PaymentReport
{
    protected $em;
    protected $repo;
    protected $fileReader;
    protected $fileFinder;
    protected $businessDaysCalc;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "fileReader" = @DI\Inject("reader.csv"),
     *     "fileFinder" = @DI\Inject("payment.report.finder"),
     *     "businessDaysCalc" = @DI\Inject("business_days_calculator"),
     * })
     */
    public function __construct($em, $fileReader, $fileFinder, $businessDaysCalc)
    {
        $this->em = $em;
        $this->repo = $this->em->getRepository('RjDataBundle:Heartland');
        $this->fileReader = $fileReader;
        $this->fileFinder = $fileFinder;
        $this->businessDaysCalc = $businessDaysCalc;
    }

    /**
     * Returns the amount of synchronized payments.
     *
     * @return int
     */
    public function synchronize($makeArchive = false)
    {
        if ($file = $this->fileFinder->find()) {

            $data = $this->fileReader->read($file);

            foreach ($data as $paymentData) {
                switch ($paymentData['TransactionType']) {
                    case 'Payment':
                        $this->processCompletePayment($paymentData);
                        break;
                    case 'Payment Return':
                        $this->processReturnedPayment($paymentData);
                        break;
                }
            }

            if ($makeArchive) {
                $this->fileFinder->archive($file);
            }

            return count($data);
        }

        return 0;
    }

    protected function processCompletePayment($paymentData)
    {
        $transactionId = $paymentData['TransactionID'];
        $transaction = $this->repo->findOneBy(array('transactionId' => $transactionId));

        if ($transaction && $batchId = $paymentData['BatchID']) {
            $transaction->setBatchId($batchId);
            $batchDate = $this->getBatchDate($transaction);
            $transaction->setBatchDate($batchDate);

            $order = $transaction->getOrder();
            $order->setStatus(OrderStatus::COMPLETE);

            $this->em->flush();
        }
    }

    protected function processReturnedPayment($paymentData)
    {
        $transactionId = $paymentData['OriginalTransactionID'];
        $transaction = $this->repo->findOneBy(array('transactionId' => $transactionId));

        // @TODO: process 'else' case in future
        if ($transaction) {
            $order = $transaction->getOrder();
            $order->setStatus(OrderStatus::RETURNED);

            $this->em->flush();
        }
    }

    protected function getBatchDate(HeartlandTransaction $transaction)
    {
        $batchDate = new DateTime();
        $batchDate->modify('-1 day');

        $paymentType = $transaction->getOrder()->getType();

        switch ($paymentType) {
            case OrderType::HEARTLAND_CARD:
                return $this->businessDaysCalc->getCreditCardBusinessDate($batchDate);
            case OrderType::HEARTLAND_BANK:
                return $this->businessDaysCalc->getACHBusinessDate($batchDate);
            default:
                return $batchDate;
        }
    }
}
