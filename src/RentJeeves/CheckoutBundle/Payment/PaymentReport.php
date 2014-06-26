<?php

namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;
use JMS\DiExtraBundle\Annotation as DI;
use DateTime;
use RuntimeException;

/**
 * @DI\Service("payment.report")
 */
class PaymentReport
{
    const TRANSACTION_TYPE_PAYMENT = 'Payment';
    const TRANSACTION_TYPE_PAYMENT_RETURN = 'Payment Return';   // with 1 space
    const TRANSACTION_TYPE_PAYMENT__RETURN = 'Payment  Return'; // with 2 spaces
    const TRANSACTION_TYPE_PAYMENT_REFUND = 'Payment  Refund';
    const TRANSACTION_TYPE_PAYMENT_VOID = 'Payment  Void';

    protected $em;
    protected $repo;
    protected $fileReader;
    protected $fileFinder;
    protected $businessDaysCalc;
    protected $batchDate;

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
        $this->batchDate = new DateTime();
    }

    /**
     * Returns the amount of synchronized payments.
     *
     * @return int
     */
    public function synchronize($makeArchive = false)
    {
        if (!$file = $this->fileFinder->find()) {
            return 0;
        }

        $this->setBatchDate($file);
        $data = $this->fileReader->read($file);

        foreach ($data as $paymentData) {
            switch ($paymentData['TransactionType']) {
                case self::TRANSACTION_TYPE_PAYMENT:
                    $this->processCompletePayment($paymentData, $data);
                    break;
                case self::TRANSACTION_TYPE_PAYMENT_RETURN:
                case self::TRANSACTION_TYPE_PAYMENT__RETURN:
                    $this->processReturnedPayment($paymentData);
                    break;
                case self::TRANSACTION_TYPE_PAYMENT_REFUND:
                    $this->processRefundedPayment($paymentData);
                    break;
                case self::TRANSACTION_TYPE_PAYMENT_VOID:
                    $this->processCancelledPayment($paymentData);
                    break;
            }
        }

        if ($makeArchive) {
            $this->fileFinder->archive($file);
        }

        return count($data);
    }

    protected function processCompletePayment($paymentData, $data)
    {
        $reversedPayment = array_filter(
            $data,
            function ($transaction) use ($paymentData) {
                if ($transaction['OriginalTransactionID'] == $paymentData['TransactionID'] &&
                    $transaction['TransactionType'] != $paymentData['TransactionType']
                ) {
                    return true;
                }
                return false;
            }
        );
        if (!$reversedPayment) {

            $transaction = $this->findTransaction($paymentData['TransactionID']);

            if ($transaction && $batchId = $paymentData['BatchID']) {
                $transaction->setBatchId($batchId);

                $transaction->setBatchDate($this->batchDate);

                $depositDate = $this->getDepositDate($transaction);
                $transaction->setDepositDate($depositDate);

                $order = $transaction->getOrder();
                $order->setStatus(OrderStatus::COMPLETE);

                $this->em->flush();
            }
        }
    }

    protected function processReturnedPayment($paymentData)
    {
        $transaction = $this->findTransaction($paymentData['OriginalTransactionID']);

        // @TODO: process 'else' case in future
        if ($transaction) {
            $order = $transaction->getOrder();
            $order->setStatus(OrderStatus::RETURNED);

            $this->em->flush();
        }
    }

    protected function processRefundedPayment($paymentData)
    {
        $transaction = $this->findTransaction($paymentData['OriginalTransactionID']);

        // @TODO: process 'else' case in future
        if ($transaction) {
            $order = $transaction->getOrder();
            $order->setStatus(OrderStatus::REFUNDED);

            $this->em->flush();
        }
    }

    protected function processCancelledPayment($paymentData)
    {
        $transaction = $this->findTransaction($paymentData['OriginalTransactionID']);

        // @TODO: process 'else' case in future
        if ($transaction) {
            $order = $transaction->getOrder();
            $order->setStatus(OrderStatus::CANCELLED);

            $this->em->flush();
        }
    }

    protected function findTransaction($transactionId)
    {
        return $this->repo->findOneBy(array('transactionId' => $transactionId));
    }

    protected function getDepositDate(HeartlandTransaction $transaction)
    {
        $depositDate = clone $this->batchDate;

        $paymentType = $transaction->getOrder()->getType();

        switch ($paymentType) {
            case OrderType::HEARTLAND_CARD:
                return $this->businessDaysCalc->getCreditCardBusinessDate($depositDate);
            case OrderType::HEARTLAND_BANK:
                return $this->businessDaysCalc->getACHBusinessDate($depositDate);
            default:
                return $depositDate;
        }
    }

    protected function setBatchDate($filename)
    {
        $filenameTokens = explode('-', $filename);
        if (!(
            isset($filenameTokens[1]) && $this->batchDate = DateTime::createFromFormat('Ymd', $filenameTokens[1])
        )) {
            throw new RuntimeException('Report filename doesn\'t correspond with required format.');
        }
    }
}
