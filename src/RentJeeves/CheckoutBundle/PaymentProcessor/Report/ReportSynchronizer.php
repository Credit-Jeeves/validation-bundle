<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;
use RentJeeves\DataBundle\Enum\TransactionStatus;

/**
 * @DI\Service("payment_processor.report_synchronizer")
 */
class ReportSynchronizer
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Synchronizes payment processor report's data.
     */
    public function synchronize($report)
    {
        switch ($report) {
            case $report instanceof DepositReport:
                $this->processDepositReport($report);
                break;
            case $report instanceof ReversalReport:
                $this->processReversalReport($report);
                break;
            default:
                throw new \Exception('Unknown report type to synchronize');
        }

        return count($report->getTransactions());
    }

    /**
     * @param DepositReport $depositReport
     */
    protected function processDepositReport(DepositReport $depositReport)
    {
        foreach ($depositReport->getTransactions() as $transaction) {
            $this->processDeposit($transaction);
        }
    }

    /**
     * @param ReversalReport $reversalReport
     */
    protected function processReversalReport(ReversalReport $reversalReport)
    {
        /** @var ReversalReportTransaction $transaction */
        foreach ($reversalReport->getTransactions() as $transaction) {
            switch ($transaction->getTransactionType()) {
                case ReversalReportTransaction::TYPE_RETURN:
                    $this->processReturned($transaction);
                    break;
                case ReversalReportTransaction::TYPE_REFUND:
                    $this->processRefunded($transaction);
                    break;
                case ReversalReportTransaction::TYPE_CANCEL:
                    $this->processCancelled($transaction);
                    break;
                case ReversalReportTransaction::TYPE_COMPLETE:
                    // double check batch ids for successful transactions
                    $this->fillEmptyBatchId($transaction);
            }
        }
    }

    /**
     * @param DepositReportTransaction $reportTransaction
     */
    protected function processDeposit(DepositReportTransaction $reportTransaction)
    {
        /** @var HeartlandTransaction $transaction */
        if (!$transaction = $this->findTransaction($reportTransaction->getTransactionID())) {
            return;
        }

        if ($batchCloseDate = $reportTransaction->getBatchCloseDate()) {
            $transaction->setBatchDate($batchCloseDate);
        }

        if ($reportTransaction->getDepositAmount() > 0 && $reportDepositDate = $reportTransaction->getDepositDate()) {
            $transaction->getOrder()->setStatus(OrderStatus::COMPLETE);
            $depositDate = BusinessDaysCalculator::getNextBusinessDate($reportDepositDate);
            $transaction->setDepositDate($depositDate);
        }
        $this->em->flush();
    }

    /**
     * Returned payment may happen before or after the payment was deposited.
     *
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processReturned(ReversalReportTransaction $reportTransaction)
    {
        /** @var HeartlandTransaction $originalTransaction */
        $originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionID());

        if ($originalTransaction) {
            $order = $originalTransaction->getOrder();
            $order->setStatus(OrderStatus::RETURNED);
            $reversalTransaction = $this->createReversalTransaction($order, $reportTransaction);
            $reversalTransaction->setBatchId($reportTransaction->getBatchID());
            $originalDepositDate = $originalTransaction->getDepositDate();
            // if original deposit date exists, set reversal deposit date
            if ($originalDepositDate) {
                $reversalDepositDate = BusinessDaysCalculator::getNextBusinessDate(
                    $reportTransaction->getTransactionDate()
                );
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
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processRefunded(ReversalReportTransaction $reportTransaction)
    {
        /** @var HeartlandTransaction $originalTransaction */
        $originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionID());

        if ($originalTransaction) {
            $order = $originalTransaction->getOrder();
            $order->setStatus(OrderStatus::REFUNDED);
            $voidedTransaction = $this->createReversalTransaction($order, $reportTransaction);
            // For reversal, from Heartland:
            // "The funds would be removed from the merchant’s account on the next business day.
            // If processed on a Saturday, it would be deducted on Monday."
            $depositDate = BusinessDaysCalculator::getNextBusinessDate($reportTransaction->getTransactionDate());
            $voidedTransaction->setDepositDate($depositDate);
            $voidedTransaction->setBatchId(null);

            $this->em->persist($voidedTransaction);
            $this->em->flush();
        }
    }

    /**
     * Voided payment may happen only before it was deposited.
     *
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processCancelled(ReversalReportTransaction $reportTransaction)
    {
        /** @var HeartlandTransaction $originalTransaction */
        $originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionID());

        if ($originalTransaction) {
            $order = $originalTransaction->getOrder();
            $order->setStatus(OrderStatus::CANCELLED);
            $originalTransaction->setDepositDate(null);
            $voidedTransaction = $this->createReversalTransaction($order, $reportTransaction);

            $this->em->persist($voidedTransaction);
            $this->em->flush();
        }
    }

    /**
     * @param $transactionId
     * @return HeartlandTransaction
     */
    protected function findTransaction($transactionId)
    {
        return $this->em->getRepository('RjDataBundle:Heartland')->findOneByTransactionId($transactionId);
    }

    /**
     * @param Order $order
     * @param ReversalReportTransaction $reportTransaction
     * @return HeartlandTransaction
     */
    protected function createReversalTransaction(Order $order, ReversalReportTransaction $reportTransaction)
    {
        $transaction = new HeartlandTransaction();
        $transaction->setTransactionId($reportTransaction->getTransactionID());
        $transaction->setOrder($order);
        $transaction->setAmount($reportTransaction->getAmountAppliedToBill());
        $transaction->setIsSuccessful(true);
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setMessages($reportTransaction->getReversalDescription());

        if ($batchId = $reportTransaction->getBatchID()) {
            $transaction->setBatchId($batchId);
        }

        return $transaction;
    }

    /**
     * @param ReversalReportTransaction $transaction
     */
    protected function fillEmptyBatchId(ReversalReportTransaction $transaction)
    {
        if (!$batchId = $transaction->getBatchID()) {
            return;
        }

        /** @var HeartlandTransaction $transaction */
        $transaction = $this->findTransaction($transaction->getTransactionId());

        if ($transaction && !$transaction->getBatchId()) {
            $transaction->setBatchId($batchId);
            $this->em->flush($transaction);
        }
    }
}
