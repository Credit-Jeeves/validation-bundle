<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Batch;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Payment;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Report as ResponseReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;

class ResponseParser extends AbstractParser
{
    const STATUS_READY_TO_DISBURSE = 'READY TO DISBURSE';

    /**
     * @return string
     */
    protected function getDeserializationModel()
    {
        return 'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Report';
    }

    /**
     * @param ResponseReport $report
     *
     * @return PaymentProcessorReportTransaction[]
     */
    protected function getTransactionsFromReport($report)
    {
        $transactions = [];
        /** @var Batch $batch */
        foreach ($report->getBatches() as $batch) {
            /** @var Payment $payment */
            foreach ($batch->getPayments() as $payment) {
                if ($payment->getResponseCode() !== self::STATUS_READY_TO_DISBURSE) {
                    $this->logger->emergency(sprintf(
                        'ERRORCODE value different from the expected value.
                        PAYMENTID : %s,
                        TRNAMT: %s,
                        BATCHID: %s,
                        DTDUE: %s,
                        ERRORCODE: %s,
                        ERRORMESSAGE: %s.',
                        $payment->getTransactionId(),
                        $payment->getAmount(),
                        $batch->getBatchId(),
                        $payment->getBatchCloseDate()->format('ymd'),
                        $payment->getResponseCode(),
                        $payment->getResponseMessage()
                    ));
                    continue;
                }
                $newTransaction = new PayDirectResponseReportTransaction();
                $newTransaction->setAmount($payment->getAmount());
                $newTransaction->setBatchId($batch->getBatchId());
                $newTransaction->setTransactionId($payment->getTransactionId());
                $newTransaction->setBatchCloseDate($payment->getBatchCloseDate());
                $newTransaction->setResponseCode($payment->getResponseCode());
                $newTransaction->setResponseMessage($payment->getResponseMessage());

                $transactions[] = $newTransaction;
            }
        }

        return $transactions;
    }
}
