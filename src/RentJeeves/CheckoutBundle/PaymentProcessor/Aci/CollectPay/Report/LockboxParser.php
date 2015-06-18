<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\Report;

use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciReportException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction;
use RentJeeves\CoreBundle\DateTime;

/**
 * Parses Lockbox payment report from ACI in the csv format.
 */
class LockboxParser implements CollectPayParserInterface
{
    const RECORD_PAYMENT_DETAIL = '6';

    const PAYMENT_CREDIT = 'C';
    const PAYMENT_DEBIT = 'D';

    const KEY_RECORD_TYPE = 0;
    const KEY_CREDIT_DEBIT_MODE = 3;
    const KEY_TRANSACTION_AMOUNT = 6;
    const KEY_TRANSACTION_DATE = 12;
    const KEY_REMIT_DATE = 13;
    const KEY_CONFIRMATION_NUMBER = 14;
    const KEY_ORIGINAL_CONFIRMATION_NUMBER = 18;
    const KEY_RETURN_CODE = 19;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AciReportException
     */
    public function parse($data)
    {
        $transactions = [];

        $decodedLockboxData = $this->decodeCsv($data);

        foreach ($decodedLockboxData as $reportRecord) {
            try {
                if (!$this->isPaymentDetailRecord($reportRecord)) {
                    continue;
                }

                $paymentType = $this->getRecordField($reportRecord, self::KEY_CREDIT_DEBIT_MODE);
                switch ($paymentType) {
                    case self::PAYMENT_CREDIT:
                        $transactions[] = $this->getCreditTransaction($reportRecord);
                        break;
                    case self::PAYMENT_DEBIT:
                        $transactions[] = $this->getDebitTransaction($reportRecord);
                        break;
                    default:
                        $this->logger->alert(sprintf('ACI: Unknown payment type %s in report', $paymentType));
                }
            } catch (\Exception $e) {
                $this->logger->alert(sprintf('ACI: Unexpected error: %s', $e->getMessage()));
            }
        }

        if (count($transactions) === 0) {
            $this->logger->alert('ACI: Lockbox parser found no transactions in the lockbox data');
        }

        return $transactions;
    }

    /**
     * @param  string $lockboxData
     *
     * @return array
     */
    protected function decodeCsv($lockboxData)
    {
        $csv = array_map('str_getcsv', explode(PHP_EOL, trim($lockboxData)));

        return $csv;
    }

    /**
     * @param  array $record
     *
     * @return bool
     *
     * @throws AciReportException
     */
    protected function isPaymentDetailRecord(array $record)
    {
        return self::RECORD_PAYMENT_DETAIL == $this->getRecordField($record, self::KEY_RECORD_TYPE);
    }

    /**
     * @param  array $record
     * @param  int $fieldKeyNumber
     *
     * @return string
     *
     * @throws AciReportException
     */
    protected function getRecordField(array $record, $fieldKeyNumber)
    {
        if (!isset($record[$fieldKeyNumber])) {
            throw new AciReportException(sprintf('ACI: Field #%s not found in report', $fieldKeyNumber));
        }

        return $record[$fieldKeyNumber];
    }

    /**
     * @param  array $record
     *
     * @return DepositReportTransaction
     *
     * @throws AciReportException
     */
    protected function getCreditTransaction(array $record)
    {
        $transaction = new DepositReportTransaction();
        $transaction
            ->setTransactionId($this->getRecordField($record, self::KEY_CONFIRMATION_NUMBER))
            ->setAmount($this->getRecordField($record, self::KEY_TRANSACTION_AMOUNT));

        $depositDate = $this->getRecordField($record, self::KEY_REMIT_DATE);
        if (!empty($depositDate)) {
            $transaction->setDepositDate(\DateTime::createFromFormat('mdY', $depositDate));
        }

        return $transaction;
    }

    /**
     * @param array $record
     *
     * @return ReversalReportTransaction
     *
     * @throws AciReportException
     */
    protected function getDebitTransaction(array $record)
    {
        $transaction = new ReversalReportTransaction();
        $transaction
            ->setTransactionId($this->getRecordField($record, self::KEY_CONFIRMATION_NUMBER))
            ->setAmount($this->getRecordField($record, self::KEY_TRANSACTION_AMOUNT))
            ->setOriginalTransactionId($this->getRecordField($record, self::KEY_ORIGINAL_CONFIRMATION_NUMBER))
            ->setTransactionType($this->getDebitTransactionType($record));

        $reversalDescription = sprintf(
            '%s : %s',
            self::KEY_RETURN_CODE,
            ReturnCode::getCodeMessage($this->getRecordField($record, self::KEY_RETURN_CODE))
        );
        $transaction->setReversalDescription($reversalDescription);

        $depositDate = $this->getRecordField($record, self::KEY_REMIT_DATE);
        if (!empty($depositDate)) {
            $transaction->setTransactionDate(DateTime::createFromFormat('mdY', $depositDate));
        }

        return $transaction;
    }

    /**
     * Returns REFUND type if isset originalConfirmationNumber. In other case returns RETURN type.
     *
     * @param array $record
     *
     * @return string
     */
    protected function getDebitTransactionType(array $record)
    {
        $confirmationNumber = $this->getRecordField($record, self::KEY_CONFIRMATION_NUMBER);
        $originalConfirmationNumber = $this->getRecordField($record, self::KEY_ORIGINAL_CONFIRMATION_NUMBER);
        if (!empty($confirmationNumber) && !empty($originalConfirmationNumber) &&
            $confirmationNumber !== $originalConfirmationNumber
        ) {
            return ReversalReportTransaction::TYPE_REFUND;
        }

        return ReversalReportTransaction::TYPE_RETURN;
    }
}
