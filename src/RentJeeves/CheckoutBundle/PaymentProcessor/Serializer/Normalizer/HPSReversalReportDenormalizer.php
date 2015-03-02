<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Serializer\Normalizer;

use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class HPSReversalReportDenormalizer implements DenormalizerInterface
{
    const FORMAT = 'hps_csv_file';

    const TRANSACTION_TYPE_PAYMENT = 'Payment';
    const TRANSACTION_TYPE_PAYMENT_RETURN = 'Payment Return';   // with 1 space
    const TRANSACTION_TYPE_PAYMENT__RETURN = 'Payment  Return'; // with 2 spaces
    const TRANSACTION_TYPE_PAYMENT_REFUND = 'Payment  Refund';
    const TRANSACTION_TYPE_PAYMENT_VOID = 'Payment  Void';
    const TRANSACTION_TYPE_PAYMENT_REVERSAL = 'Payment Reversal';
    const TRANSACTION_REVERSAL_CODE_CREDIT = 'Crdt';
    const TRANSACTION_REVERSAL_CODE_VOID = 'Void';

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $report = new ReversalReport();

        foreach ($data as $transaction) {
            $reversalTransaction = new ReversalReportTransaction();

            $reversalTransaction
                ->setBatchID($transaction['BatchID'])
                ->setAmountAppliedToBill($transaction['AmountAppliedToBill'])
                ->setTransactionID($transaction['TransactionID'])
                ->setOriginalTransactionID($transaction['OriginalTransactionID'])
                ->setTransactionDate(
                    $transaction['TransactionDate'] ? new DateTime($transaction['TransactionDate']) : null
                )
                ->setTransactionType($this->getTransactionType($transaction))
                ->setReversalDescription($transaction['ReversalCodeDescription']);

            $report->addTransaction($reversalTransaction);
        }

        return $report;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return self::FORMAT === $format && is_array($data);
    }

    protected function getTransactionType(array $transactionData)
    {
        switch ($transactionData['TransactionType']) {
            case self::TRANSACTION_TYPE_PAYMENT_RETURN:
            case self::TRANSACTION_TYPE_PAYMENT__RETURN:
                return ReversalReportTransaction::TYPE_RETURN;
            case self::TRANSACTION_TYPE_PAYMENT_REFUND:
                return ReversalReportTransaction::TYPE_REFUND;
            case self::TRANSACTION_TYPE_PAYMENT_VOID:
                return ReversalReportTransaction::TYPE_CANCEL;
            case self::TRANSACTION_TYPE_PAYMENT_REVERSAL:
                if (self::TRANSACTION_REVERSAL_CODE_CREDIT == $transactionData['ReversalCode']) {
                    return ReversalReportTransaction::TYPE_REFUND;
                }
                if (self::TRANSACTION_REVERSAL_CODE_VOID == $transactionData['ReversalCode']) {
                    return ReversalReportTransaction::TYPE_CANCEL;
                }
                break;
            case self::TRANSACTION_TYPE_PAYMENT:
                return ReversalReportTransaction::TYPE_COMPLETE;
            default:
                throw new \Exception('HPS: Unknown transaction type in report.');
        }
    }
}
