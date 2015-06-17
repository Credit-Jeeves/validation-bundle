<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Batch;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Payment;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Report as ResponseReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;

class ResponseParser extends AbstractParser
{
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
