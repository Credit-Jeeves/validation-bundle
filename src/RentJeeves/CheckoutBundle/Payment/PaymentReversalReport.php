<?php

namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\TransactionStatus;

/**
 * @DI\Service("payment.reversal_report")
 */
class PaymentReversalReport implements PaymentSynchronizerInterface
{
    const TRANSACTION_TYPE_PAYMENT = 'Payment';
    const TRANSACTION_TYPE_PAYMENT_RETURN = 'Payment Return';   // with 1 space
    const TRANSACTION_TYPE_PAYMENT__RETURN = 'Payment  Return'; // with 2 spaces
    const TRANSACTION_TYPE_PAYMENT_REFUND = 'Payment  Refund';
    const TRANSACTION_TYPE_PAYMENT_VOID = 'Payment  Void';
    const TRANSACTION_TYPE_PAYMENT_REVERSAL = 'Payment Reversal';
    const TRANSACTION_REVERSAL_CODE_CREDIT = 'Crdt';
    const TRANSACTION_REVERSAL_CODE_VOID = 'Void';

    const REPORT_FILENAME_SUFFIX = 'BillDataExport';

    protected $em;
    protected $repo;
    protected $fileReader;
    protected $fileFinder;
    /**
     * @var BusinessDaysCalculator
     */
    protected $businessDaysCalculator;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "fileReader" = @DI\Inject("reader.csv"),
     *     "fileFinder" = @DI\Inject("payment.report.finder"),
     *     "businessDaysCalc" = @DI\Inject("business_days_calculator")
     * })
     */
    public function __construct($em, $fileReader, $fileFinder, BusinessDaysCalculator $businessDaysCalc)
    {
        $this->em = $em;
        $this->repo = $this->em->getRepository('RjDataBundle:Heartland');
        $this->fileReader = $fileReader;
        $this->fileFinder = $fileFinder;
        $this->businessDaysCalculator = $businessDaysCalc;
    }

    /**
     * Returns the amount of synchronized payments.
     *
     * @return int
     */
    public function synchronize($makeArchive = false)
    {
        if (!$file = $this->fileFinder->findBySuffix(self::REPORT_FILENAME_SUFFIX)) {
            return 0;
        }

        $data = $this->fileReader->read($file);

        foreach ($data as $paymentData) {
            switch ($paymentData['TransactionType']) {
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
                case self::TRANSACTION_TYPE_PAYMENT_REVERSAL:
                    if (self::TRANSACTION_REVERSAL_CODE_CREDIT == $paymentData['ReversalCode']) {
                        $this->processRefundedPayment($paymentData);
                    }
                    if (self::TRANSACTION_REVERSAL_CODE_VOID == $paymentData['ReversalCode']) {
                        $this->processCancelledPayment($paymentData);
                    }
                    break;
                case self::TRANSACTION_TYPE_PAYMENT:
                    // double check batch ids for successful transactions
                    $this->fillEmptyBatchId($paymentData);
            }
        }

        if ($makeArchive) {
            $this->fileFinder->archive($file, self::REPORT_FILENAME_SUFFIX);
        }

        return count($data);
    }

    /**
     * Returned payment may happen before or after the payment was deposited.
     *
     * @param array $paymentData
     */
    protected function processReturnedPayment(array $paymentData)
    {
        /** @var HeartlandTransaction $originalTransaction */
        $originalTransaction = $this->findTransaction($paymentData['OriginalTransactionID']);

        if ($originalTransaction) {
            $order = $originalTransaction->getOrder();
            $order->setStatus(OrderStatus::RETURNED);
            $reversalTransaction = $this->createReversalTransaction($order, $paymentData);
            $reversalTransaction->setBatchId($paymentData['BatchID']);
            $originalDepositDate = $originalTransaction->getDepositDate();
            // if original deposit date exists, set reversal deposit date
            if ($originalDepositDate) {
                $transactionDate = new DateTime($paymentData['TransactionDate']);
                $reversalDepositDate = $this->businessDaysCalculator->getNextBusinessDate($transactionDate);
                $reversalTransaction->setDepositDate($reversalDepositDate);
                $reversalTransaction->setBatchId(null);
            }

            $this->em->persist($reversalTransaction);
            $this->em->flush();
        }
    }

    /**
     * Refunded payment may happen only after the payment was deposited.
     *
     * @param array$paymentData
     */
    protected function processRefundedPayment($paymentData)
    {
        $originalTransaction = $this->findTransaction($paymentData['OriginalTransactionID']);

        if ($originalTransaction) {
            $order = $originalTransaction->getOrder();
            $order->setStatus(OrderStatus::REFUNDED);
            $voidTransaction = $this->createReversalTransaction($order, $paymentData);
            $transactionDate = new DateTime($paymentData['TransactionDate']);
            // For reversal, from Heartland:
            // "The funds would be removed from the merchant’s account on the next business day.
            // If processed on a Saturday, it would be deducted on Monday."
            $depositDate = $this->businessDaysCalculator->getNextBusinessDate($transactionDate);
            $voidTransaction->setDepositDate($depositDate);
            $voidTransaction->setBatchId(null);

            $this->em->persist($voidTransaction);
            $this->em->flush();
        }
    }

    /**
     * Voided payment may happen only before it was deposited.
     *
     * @param array $paymentData
     */
    protected function processCancelledPayment($paymentData)
    {
        /** @var HeartlandTransaction $originalTransaction */
        $originalTransaction = $this->findTransaction($paymentData['OriginalTransactionID']);

        if ($originalTransaction) {
            $order = $originalTransaction->getOrder();
            $order->setStatus(OrderStatus::CANCELLED);
            $originalTransaction->setDepositDate(null);
            $voidTransaction = $this->createReversalTransaction($order, $paymentData);

            $this->em->persist($voidTransaction);
            $this->em->flush();
        }
    }

    protected function findTransaction($transactionId)
    {
        return $this->repo->findOneBy(array('transactionId' => $transactionId));
    }

    protected function createReversalTransaction(Order $order, array $paymentData)
    {
        $transaction = new HeartlandTransaction();
        $transaction->setTransactionId($paymentData['TransactionID']);
        $transaction->setOrder($order);
        $transaction->setAmount($paymentData['AmountAppliedToBill']);
        $transaction->setIsSuccessful(true);
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setMessages($paymentData['ReversalCodeDescription']);
        $transaction->setMerchantName($paymentData['MerchantName']);
        if ($paymentData['BatchID']) {
            $transaction->setBatchId($paymentData['BatchID']);
        }

        return $transaction;
    }

    protected function fillEmptyBatchId($paymentData)
    {
        if (!$paymentData['BatchID']) {
            return;
        }
        /** @var HeartlandTransaction $transaction */
        $transaction = $this->findTransaction($paymentData['TransactionID']);

        if ($transaction && !$transaction->getBatchId()) {
            $transaction->setBatchId($paymentData['BatchID']);
            $this->em->flush($transaction);
        }
    }
}
