<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Monolog\Logger;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;
use RentJeeves\DataBundle\Enum\TransactionStatus;

/**
 * @DI\Service("payment_processor.report_synchronizer")
 */
class ReportSynchronizer
{
    /** @var EntityManager */
    protected $em;

    /** @var Logger */
    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Synchronizes payment processor report's data.
     *
     * @param PaymentProcessorReport $report
     * @return int
     * @throws \Exception
     */
    public function synchronize(PaymentProcessorReport $report)
    {
        if (!$report->getTransactions()) {
            $this->logger->alert('Report synchronizer: No transactions in payment processor report');
            return 0;
        }

        /** @var PaymentProcessorReportTransaction $reportTransaction */
        foreach ($report->getTransactions() as $reportTransaction) {
            switch ($reportTransaction) {
                case $reportTransaction instanceof DepositReportTransaction:
                    $this->processDepositTransaction($reportTransaction);
                    break;
                case $reportTransaction instanceof ReversalReportTransaction:
                    $this->processReversalTransaction($reportTransaction);
                    break;
                default:
                    throw new \Exception('Report synchronizer: Unknown report transaction type to synchronize');
            }
        }

        return count($report->getTransactions());
    }

    /**
     * @param ReversalReportTransaction $reportTransaction
     *
     * @return bool
     */
    protected function isAlreadyProcessedReversal(ReversalReportTransaction $reportTransaction)
    {
        $transaction = $this->em->getRepository('RjDataBundle:Heartland')
            ->findOneBy([
                'transactionId' => $reportTransaction->getTransactionId(),
                'status' => TransactionStatus::REVERSED,
            ]);
        if ($transaction) {
            return true;
        }

        return false;
    }

    /**
     * @param ReversalReportTransaction $transaction
     */
    protected function processReversalTransaction(ReversalReportTransaction $transaction)
    {
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

    /**
     * @param DepositReportTransaction $reportTransaction
     */
    protected function processDepositTransaction(DepositReportTransaction $reportTransaction)
    {
        $this->logger->debug('Processing DEPOSITED transaction with ID ' . $reportTransaction->getTransactionId());
        /** @var HeartlandTransaction $transaction */
        if (!$transaction = $this->findTransaction($reportTransaction->getTransactionId())) {
            $this->logger->alert('Deposit transaction ID ' . $reportTransaction->getTransactionId() . ' not found');
            return;
        }

        // if transaction doesn't have batch date, but report transaction does - then update it
        if (!$transaction->getBatchDate() && $batchCloseDate = $reportTransaction->getBatchCloseDate()) {
            $transaction->setBatchDate($batchCloseDate);
        }

        if ($reportTransaction->getAmount() > 0 && $reportDepositDate = $reportTransaction->getDepositDate()) {
            $transaction->getOrder()->setStatus(OrderStatus::COMPLETE);
            if (!$transaction->getDepositDate()) {
                $depositDate = BusinessDaysCalculator::getNextBusinessDate($reportDepositDate);
                $transaction->setDepositDate($depositDate);
            }
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
        if ($this->isAlreadyProcessedReversal($reportTransaction)) {
            $this->logger->debug(
                'Returned transaction ID ' . $reportTransaction->getTransactionId() . ' is already processed'
            );
            return;
        }

        $this->logger->debug(
            'Processing RETURNED transaction with original transaction ID ' .
            $reportTransaction->getOriginalTransactionId()
        );
        /** @var HeartlandTransaction $originalTransaction */
        if (!$originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionId())) {
            $this->logger->alert(
                'Returned transaction ID ' . $reportTransaction->getOriginalTransactionId() . ' not found'
            );
            return;
        }

        $order = $originalTransaction->getOrder();
        $order->setStatus(OrderStatus::RETURNED);
        $reversalTransaction = $this->createReversalTransaction($order, $reportTransaction);
        $reversalTransaction->setBatchId($reportTransaction->getBatchId());
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

    /**
     * Refunded payment may happen only after the payment was deposited.
     *
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processRefunded(ReversalReportTransaction $reportTransaction)
    {
        if ($this->isAlreadyProcessedReversal($reportTransaction)) {
            $this->logger->debug(
                'Refunded transaction ID ' . $reportTransaction->getTransactionId() . ' is already processed'
            );
            return;
        }

        $this->logger->debug(
            'Processing REFUNDED transaction with original transaction ID ' .
            $reportTransaction->getOriginalTransactionId()
        );
        /** @var HeartlandTransaction $originalTransaction */
        if (!$originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionId())) {
            $this->logger->alert(
                'Refunded transaction ID ' . $reportTransaction->getOriginalTransactionId() . ' not found'
            );
            return;
        }

        $order = $originalTransaction->getOrder();
        $order->setStatus(OrderStatus::REFUNDED);
        $voidedTransaction = $this->createReversalTransaction($order, $reportTransaction);
        // For reversal, from Heartland:
        // "The funds would be removed from the merchant’s account on the next business day.
        // If processed on a Saturday, it would be deducted on Monday."
        // TODO: may be moved down to payment processor layer if this is different
        $depositDate = BusinessDaysCalculator::getNextBusinessDate($reportTransaction->getTransactionDate());
        $voidedTransaction->setDepositDate($depositDate);
        $voidedTransaction->setBatchId(null);

        $this->em->persist($voidedTransaction);
        $this->em->flush();
    }

    /**
     * Voided payment may happen only before it was deposited.
     *
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processCancelled(ReversalReportTransaction $reportTransaction)
    {
        if ($this->isAlreadyProcessedReversal($reportTransaction)) {
            $this->logger->debug(
                'Cancelled transaction ID ' . $reportTransaction->getTransactionId() . ' is already processed'
            );
            return;
        }

        $this->logger->debug(
            'Processing CANCELLED transaction with original transaction ID ' .
            $reportTransaction->getOriginalTransactionId()
        );
        /** @var HeartlandTransaction $originalTransaction */
        if (!$originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionId())) {
            $this->logger->alert(
                'Cancelled transaction ID ' . $reportTransaction->getOriginalTransactionId() . ' not found'
            );
            return;
        }

        $order = $originalTransaction->getOrder();
        $order->setStatus(OrderStatus::CANCELLED);
        $originalTransaction->setDepositDate(null);
        $voidedTransaction = $this->createReversalTransaction($order, $reportTransaction);

        $this->em->persist($voidedTransaction);
        $this->em->flush();
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
        $this->logger->debug(
            'Creating REVERSED transaction for Order ID ' . $order->getId() .
            ', transaction ID ' . $reportTransaction->getTransactionId()
        );
        $transaction = new HeartlandTransaction();
        $transaction->setTransactionId($reportTransaction->getTransactionId());
        $transaction->setOrder($order);
        $transaction->setAmount($reportTransaction->getAmount());
        $transaction->setIsSuccessful(true);
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setMessages($reportTransaction->getReversalDescription());

        if ($batchId = $reportTransaction->getBatchId()) {
            $transaction->setBatchId($batchId);
        }

        return $transaction;
    }

    /**
     * @param ReversalReportTransaction $transaction
     */
    protected function fillEmptyBatchId(ReversalReportTransaction $transaction)
    {
        if (!$batchId = $transaction->getBatchId()) {
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
