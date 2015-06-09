<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Payment;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Report as AdjustmentReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Report;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectDepositReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectReversalReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;

class AdjustmentParser extends AbstractParser
{
    /**
     * @return string
     */
    protected function getDeserializeModel()
    {
        return 'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Report';
    }

    /**
     * @param AdjustmentReport $report
     *
     * @return PaymentProcessorReportTransaction[]
     */
    protected function getTransactionsFromReport($report)
    {
        $transactions = array_merge(
            [],
            $this->getDepositTransactions($report),
            $this->getReversalTransactions($report)
        );

        return $transactions;
    }

    /**
     * @param Report $report
     *
     * @return PayDirectDepositReportTransaction[]
     */
    protected function getDepositTransactions(Report $report)
    {
        $depositTransactions = [];
        /** @var Payment $payment */
        foreach ($report->getOriginator()->getDepositTransactions()->getPayments() as $payment) {
            $newDepositTransactions = new PayDirectDepositReportTransaction();
            $newDepositTransactions->setDepositDate($report->getDepositDate());
            $newDepositTransactions->setAmount($payment->getDetail()->getAmount());
            $newDepositTransactions->setTransactionId($payment->getDetail()->getTransactionId());

            $depositTransactions[] = $newDepositTransactions;
        }

        return $depositTransactions;
    }

    /**
     * @param Report $report
     *
     * @return PayDirectReversalReportTransaction[]
     */
    protected function getReversalTransactions(Report $report)
    {
        $reversalTransactions = [];
        foreach ($report->getOriginator()->getReversalTransactions() as $type => $transactions) {
            if (empty($transactions) === true) {
                continue;
            }
            /** @var Payment $payment */
            foreach ($transactions->getPayments() as $payment) {
                if ($this->getRenttrackTransactionType($type) === false) {
                    $this->logger->alert(sprintf('Items found in the node "%s"', $type));
                    break;
                }
                $newReversalTransaction = new PayDirectReversalReportTransaction();
                $newReversalTransaction->setTransactionId($payment->getDetail()->getTransactionId());
                $newReversalTransaction->setAmount($payment->getDetail()->getAmount());
                $newReversalTransaction->setTransactionDate($report->getDepositDate());
                $newReversalTransaction->setTransactionType($this->getRenttrackTransactionType($type));
                $newReversalTransaction->setReversalDescription($payment->getDetail()->getReturnCode());

                $reversalTransactions[] = $newReversalTransaction;
            }
        }

        return $reversalTransactions;
    }

    /**
     * @param string $type
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function getRenttrackTransactionType($type)
    {
        switch ($type) {
            case 'RETURNED_PAYMENTS':
            case 'STOPPED_CHECKS':
                return PayDirectReversalReportTransaction::TYPE_RETURN;
            case 'REFUNDED_SCANLINE_REJECTS':
            case 'REFUNDED_DUPLICATE_PAYMENTS':
            case 'REFUNDED_CANCELLED_PAYMENTS':
            case 'REFUNDED_OUTDATED_CHECKS':
            case 'REFUNDED_RETURNED_PAYMENTS':
            case 'REFUNDED_STOPPED_CHECKS':
                return PayDirectReversalReportTransaction::TYPE_REFUND;
            case 'REISSUED_STOPPED_CHECKS':
            case 'CORRECTED_DUPLICATE_PAYMENTS':
            case 'CORRECTED_RETURNED_PAYMENTS':
                return false;
            default:
                throw new \Exception(sprintf('%s - Wrong reversal transaction type', $type));
        }
    }
}
