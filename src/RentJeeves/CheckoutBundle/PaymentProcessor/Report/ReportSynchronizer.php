<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Entity\Transaction as HeartlandTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;
use RentJeeves\DataBundle\Enum\TransactionStatus;

class ReportSynchronizer
{
    /**
     * @var  OrderStatusManagerInterface
     */
    protected $orderStatusManager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param OrderStatusManagerInterface $orderStatusManager
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        OrderStatusManagerInterface $orderStatusManager
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->orderStatusManager = $orderStatusManager;
    }

    /**
     * Synchronizes payment processor report's data.
     *
     * @param  PaymentProcessorReport $report
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
                case $reportTransaction instanceof PayDirectDepositReportTransaction:
                    $this->processPayDirectDepositTransaction($reportTransaction);
                    break;
                case $reportTransaction instanceof PayDirectResponseReportTransaction:
                    $this->processPayDirectResponseTransaction($reportTransaction);
                    break;
                case $reportTransaction instanceof PayDirectReversalReportTransaction:
                    $this->processPayDirectReversalReportTransaction($reportTransaction);
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
        $transaction = $this->em->getRepository('RjDataBundle:Transaction')
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
        $this->logger->debug(sprintf('Deposit Transaction %s: Start sync...', $reportTransaction->getTransactionId()));

        /** @var HeartlandTransaction $transaction */
        if (!$transaction = $this->findTransaction($reportTransaction->getTransactionId())) {
            $this->logger->alert(sprintf(
                'Deposit transaction ID %s not found',
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        // if transaction doesn't have batch date, but report transaction does - then update it
        if (!$transaction->getBatchDate() && $batchCloseDate = $reportTransaction->getBatchCloseDate()) {
            $transaction->setBatchDate($batchCloseDate);
        }

        if ($reportTransaction->getAmount() > 0 && $reportDepositDate = $reportTransaction->getDepositDate()) {
            $this->orderStatusManager->setComplete($transaction->getOrder());
            if (!$transaction->getDepositDate()) {
                $depositDate = BusinessDaysCalculator::getNextBusinessDate($reportDepositDate);
                $transaction->setDepositDate($depositDate);
            }
        }
        $this->em->flush();
        $this->logger->debug(sprintf('Transaction %s:  Sync successful.', $reportTransaction->getTransactionId()));
    }

    /**
     * Returned payment may happen before or after the payment was deposited.
     *
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processReturned(ReversalReportTransaction $reportTransaction)
    {
        $this->logger->debug(sprintf('Returned transaction %s: Start sync...', $reportTransaction->getTransactionId()));

        if ($this->isAlreadyProcessedReversal($reportTransaction)) {
            $this->logger->debug(sprintf(
                'Returned transaction ID %s is already processed',
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        /** @var HeartlandTransaction $originalTransaction */
        if (!$originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionId())) {
            $this->logger->alert(sprintf(
                'Returned transaction ID %s not found',
                $reportTransaction->getOriginalTransactionId()
            ));

            return;
        }

        $order = $originalTransaction->getOrder();
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

        // this needs to run after the reversalTransaction is persisted
        $this->orderStatusManager->setReturned($order);

        $this->logger->debug(sprintf(
            'Returned transaction %s: Sync successful.',
            $reportTransaction->getTransactionId()
        ));
    }

    /**
     * Refunded payment may happen only after the payment was deposited.
     *
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processRefunded(ReversalReportTransaction $reportTransaction)
    {
        $this->logger->debug(sprintf('Refunded transaction %s: Start sync...', $reportTransaction->getTransactionId()));

        if ($this->isAlreadyProcessedReversal($reportTransaction)) {
            $this->logger->debug(sprintf(
                'Refunded transaction ID %s is already processed',
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        /** @var HeartlandTransaction $originalTransaction */
        if (!$originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionId())) {
            $this->logger->alert(sprintf(
                'Refunded transaction ID %s not found',
                $reportTransaction->getOriginalTransactionId()
            ));

            return;
        }

        $order = $originalTransaction->getOrder();
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

        // this needs to run after the voidedTransaction is persisted
        $this->orderStatusManager->setRefunded($order);

        $this->logger->debug(sprintf(
            'Refunded transaction %s: Sync successful.',
            $reportTransaction->getTransactionId()
        ));
    }

    /**
     * Voided payment may happen only before it was deposited.
     *
     * @param ReversalReportTransaction $reportTransaction
     */
    protected function processCancelled(ReversalReportTransaction $reportTransaction)
    {
        $this->logger->debug(sprintf(
            'Cancelled transaction %s: Start sync...',
            $reportTransaction->getTransactionId()
        ));

        if ($this->isAlreadyProcessedReversal($reportTransaction)) {
            $this->logger->debug(sprintf(
                'Cancelled transaction ID %s is already processed',
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        /** @var HeartlandTransaction $originalTransaction */
        if (!$originalTransaction = $this->findTransaction($reportTransaction->getOriginalTransactionId())) {
            $this->logger->alert(sprintf(
                'Cancelled transaction ID %s not found',
                $reportTransaction->getOriginalTransactionId()
            ));

            return;
        }

        $order = $originalTransaction->getOrder();
        $originalTransaction->setDepositDate();
        $voidedTransaction = $this->createReversalTransaction($order, $reportTransaction);

        $this->em->persist($voidedTransaction);
        $this->em->flush();

        // this needs to run after the voidedTransaction is persisted
        $this->orderStatusManager->setCancelled($order);

        $this->logger->debug(sprintf(
            'Cancelled transaction %s: Sync successful.',
            $reportTransaction->getTransactionId()
        ));
    }

    /**
     * @param $transactionId
     * @return HeartlandTransaction
     */
    protected function findTransaction($transactionId)
    {
        return $this->em->getRepository('RjDataBundle:Transaction')->findOneByTransactionId($transactionId);
    }

    /**
     * @param string $transactionId
     *
     * @return \RentJeeves\DataBundle\Entity\OutboundTransaction
     */
    protected function findDepositOutboundTransaction($transactionId)
    {
        return $this->em->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => $transactionId, 'type' => OutboundTransactionType::DEPOSIT]
        );
    }

    /**
     * @param string $transactionId
     *
     * @return \RentJeeves\DataBundle\Entity\OutboundTransaction
     */
    protected function findReversalOutboundTransaction($transactionId)
    {
        return $this->em->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => $transactionId, 'type' => OutboundTransactionType::REVERSAL]
        );
    }

    /**
     * @param  Order $order
     * @param  ReversalReportTransaction $reportTransaction
     * @return HeartlandTransaction
     */
    protected function createReversalTransaction(Order $order, ReversalReportTransaction $reportTransaction)
    {
        $this->logger->debug(sprintf(
            'Creating REVERSED transaction for Order ID %s, transaction ID %s',
            $order->getId(),
            $reportTransaction->getTransactionId()
        ));
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
     * @param PayDirectDepositReportTransaction $reportTransaction
     */
    protected function processPayDirectDepositTransaction(PayDirectDepositReportTransaction $reportTransaction)
    {
        $this->logger->debug(sprintf(
            'PayDirect Deposit Transaction #%s: Start sync...',
            $reportTransaction->getTransactionId()
        ));

        if (null === $transaction = $this->findDepositOutboundTransaction($reportTransaction->getTransactionId())) {
            $this->logger->alert(sprintf(
                'Deposit Outbound Transaction #%s not found',
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        if (null !== $transaction->getDepositDate()) {
            $this->logger->alert(sprintf(
                'PayDirect Deposit Transaction #%s already has deposit date. Skipping.',
                $reportTransaction->getTransactionId()
            ));

            return;
        }
        $order = $transaction->getOrder();

        if ($order->getStatus() !== OrderStatus::SENDING && $order->getStatus() !== OrderStatus::RETURNED) {
            $this->logger->alert(sprintf(
                'Status for Order #%d must be \'%s\', \'%s\' given',
                $order->getId(),
                OrderStatus::SENDING,
                $order->getStatus()
            ));

            return;
        }

        $this->orderStatusManager->setComplete($order);
        if ($reportTransaction->getDepositDate() !== null) {
            $transaction->setDepositDate($reportTransaction->getDepositDate());
        }

        $this->em->flush();
        $this->logger->debug(sprintf(
            'PayDirect Deposit Transaction #%s:  Sync successful.',
            $transaction->getTransactionId()
        ));
    }

    /**
     * @param PayDirectReversalReportTransaction $reportTransaction
     */
    protected function processPayDirectReversalReportTransaction(PayDirectReversalReportTransaction $reportTransaction)
    {
        $this->logger->debug(sprintf(
            'PayDirect Reversal Transaction #%s: Start sync...',
            $reportTransaction->getTransactionId()
        ));

        if (null === $transaction = $this->findDepositOutboundTransaction($reportTransaction->getTransactionId())) {
            $this->logger->alert(sprintf(
                'Deposit Outbound Transaction #%s not found',
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        if (true === $this->isAlreadyProcessedPayDirectReversal($reportTransaction)) {
            $this->logger->alert(sprintf(
                'Reversal Outbound Transaction #%s is already processed',
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        $order = $transaction->getOrder();

        /* PayDirect order can go to reversal state in 2 cases:
         * 1. When order was set to complete (sending) by inbound leg and then outbound leg reverse comes
         * 2. When order was set to returned by inbound leg and then outbound leg reverse comes
         */
        if ($order->getStatus() !== OrderStatus::SENDING && $order->getStatus() !== OrderStatus::RETURNED) {
            $this->logger->alert(sprintf(
                'Unexpected order #%s status (%s) when transaction #%s processing',
                $order->getId(),
                $order->getStatus(),
                $reportTransaction->getTransactionId()
            ));

            return;
        }

        $newReversalOutboundTransaction = new OutboundTransaction();
        $newReversalOutboundTransaction->setDepositDate($reportTransaction->getTransactionDate());
        $newReversalOutboundTransaction->setTransactionId($reportTransaction->getTransactionId());
        $newReversalOutboundTransaction->setAmount($reportTransaction->getAmount());
        $newReversalOutboundTransaction->setType(OutboundTransactionType::REVERSAL);
        $newReversalOutboundTransaction->setReversalDescription($reportTransaction->getReversalDescription());
        $newReversalOutboundTransaction->setOrder($order);

        if ($reportTransaction->getTransactionType() === PayDirectReversalReportTransaction::TYPE_REFUNDING) {
            $this->orderStatusManager->setRefunded($order);
            $this->logger->alert(
                sprintf(
                    'Check#%s for Order#%d has been refunded. Need to refund to tenant via CollectV4 Client Console.
                    Transaction #%s',
                    $reportTransaction->getTransactionId(),
                    $order->getId(),
                    $transaction->getTransactionId()
                )
            );
        } elseif ($reportTransaction->getTransactionType() === PayDirectReversalReportTransaction::TYPE_REISSUED) {
            $this->orderStatusManager->setReissued($order);
            $this->logger->alert(
                sprintf(
                    'Check#%s has been reissued to \'%s\' group. No action required.',
                    $reportTransaction->getTransactionId(),
                    $order->getGroupName()
                )
            );
        } else {
            throw new \LogicException(sprintf(
                'Wrong type for PayDirectReversalReportTransaction - %s',
                $reportTransaction->getTransactionType()
            ));
        }

        $this->em->persist($newReversalOutboundTransaction);
        $this->em->flush();

        $this->logger->debug(sprintf(
            'PayDirect Response Transaction #%s:  Sync successful.',
            $transaction->getTransactionId()
        ));
    }

    /**
     * @param PayDirectResponseReportTransaction $reportTransaction
     */
    protected function processPayDirectResponseTransaction(PayDirectResponseReportTransaction $reportTransaction)
    {
        $this->logger->debug(sprintf(
            'PayDirect Response Transaction #%s: Start sync...',
            $reportTransaction->getTransactionId()
        ));

        if (null === $transaction = $this->findDepositOutboundTransaction($reportTransaction->getTransactionId())) {
            $this->logger->alert(sprintf(
                'Deposit Outbound Transaction #%s not found',
                $reportTransaction->getTransactionId()
            ));

            return;
        }
        if ($reportTransaction->getResponseCode() !== PayDirectResponseReportTransaction::PAY_DIRECT_RESPONSE_STATUS) {
            $date = $reportTransaction->getBatchCloseDate();
            $this->logger->emergency(sprintf(
                'ERRORCODE value different from the expected value.
                    PAYMENTID : %s,
                    TRNAMT: %s,
                    BATCHID: %s,
                    DTDUE: %s,
                    ERRORCODE: %s,
                    ERRORMESSAGE: %s.',
                $reportTransaction->getTransactionId(),
                $reportTransaction->getAmount(),
                $reportTransaction->getBatchId(),
                $date !== null ? $date->format('ymd') : '',
                $reportTransaction->getResponseCode(),
                $reportTransaction->getResponseMessage()
            ));

            return;
        }

        $transaction->setBatchId($reportTransaction->getBatchId());
        $transaction->setBatchCloseDate($reportTransaction->getBatchCloseDate());
        $this->em->flush($transaction);

        $this->logger->debug(sprintf(
            'PayDirect Response Transaction #%s:  Sync successful.',
            $transaction->getTransactionId()
        ));
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

    /**
     * @param PayDirectReversalReportTransaction $reportTransaction
     *
     * @return bool
     */
    protected function isAlreadyProcessedPayDirectReversal(PayDirectReversalReportTransaction $reportTransaction)
    {
        return null !== $this->findReversalOutboundTransaction($reportTransaction->getTransactionId());
    }
}
