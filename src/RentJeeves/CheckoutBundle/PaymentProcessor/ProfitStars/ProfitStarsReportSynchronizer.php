<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManager;
use RentJeeves\DataBundle\Entity\ProfitStarsTransaction;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSSettlementType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfInt;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSDisplayFields;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSPaymentOrigin;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSSettlementStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSSettlementType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSTransactionStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\TransactionReportingClient;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSAuthResponseCode;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSDisplayFields;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSEventReport;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSOperationType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSPaymentOrigin;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSPaymentType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSSettlementStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSTransactionEvent;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSTransactionStatus;

/**
 * Service`s name "payment_processor.profit_stars.report_synchronizer"
 */
class ProfitStarsReportSynchronizer
{
    /**
     * @var TransactionReportingClient
     */
    protected $client;

    /**
     * @var OrderStatusManager
     */
    protected $statusManager;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $storeKey;

    /**
     * @var array
     */
    protected $reversedOrderStatuses = [
        OrderStatus::REFUNDED,
        OrderStatus::RETURNED,
        OrderStatus::CANCELLED,
    ];

    /**
     * @param TransactionReportingClient $client
     * @param OrderStatusManager         $statusManager
     * @param EntityManagerInterface     $em
     * @param LoggerInterface            $logger
     * @param string                     $storeId
     * @param string                     $storeKey
     */
    public function __construct(
        TransactionReportingClient $client,
        OrderStatusManager $statusManager,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        $storeId,
        $storeKey
    ) {
        $this->client = $client;
        $this->statusManager = $statusManager;
        $this->em = $em;
        $this->logger = $logger;
        $this->storeId = $storeId;
        $this->storeKey = $storeKey;
    }

    /**
     * @param string    $locationId
     * @param string    $entityId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function sync($locationId, $entityId, \DateTime $startDate, \DateTime $endDate)
    {
        $this->logger->info(
            'Start synchronize transactions for ProfitStars.',
            [
                'locationId' => $locationId,
                'entityId' => $entityId,
                'date' => $startDate->format('Y-m-d')
            ]
        );

        $reports = $this->getReports($locationId, $entityId, $startDate, $endDate);
        /** @var WSEventReport $report */
        foreach ($reports as $report) {
            switch ($report->getEventType()) {
                case WSTransactionEvent::SETTLED:
                    $this->syncSettledReport($report);
                    break;
                case WSTransactionEvent::RETURNED_NSF:
                case WSTransactionEvent::OTHER_CHECK21_RETURNS:
                case WSTransactionEvent::DISPUTED:
                case WSTransactionEvent::RETURNED_BAD_ACCOUNT:
                case WSTransactionEvent::DECLINED:
                case WSTransactionEvent::PROCESSING_ERROR:
                case WSTransactionEvent::REVERSED:
                case WSTransactionEvent::NOTICE_OF_CHANGE:
                case WSTransactionEvent::VOIDED:
                case WSTransactionEvent::REFUNDED:
                    $this->syncReversalReport($report);
                    break;
                case WSTransactionEvent::SUSPENDED:
                    $this->logger->alert(sprintf('Unexpected event type "%s"', $report->getEventType()));
                    break;
                case WSTransactionEvent::RESOLVED:
                case WSTransactionEvent::ORIGINATED:
                case WSTransactionEvent::PROCESSED:
                default:
                    $this->logger->info(sprintf('Skip report with event type "%s"', $report->getEventType()));
                    break;
            }

            $this->em->clear();
        }

        $this->logger->info(
            'Synchronization is complete.',
            [
                'locationId' => $locationId,
                'entityId' => $entityId,
                'date' => $startDate->format('Y-m-d')
            ]
        );
    }

    /**
     * @param WSEventReport $report
     */
    protected function syncSettledReport(WSEventReport $report)
    {
        $this->logger->info(sprintf('Try to sync SettledReport for TransactionId#%s', $report->getReferenceNumber()));
        /** @var Transaction $transaction */
        $transaction = $this->getTransactionRepository()->findOneBy(
            [
                'status' => TransactionStatus::COMPLETE,
                'transactionId' => $report->getReferenceNumber(),
            ]
        );

        if (null === $transaction) {
            $this->logger->info(
                sprintf('Completed Transaction with transactionId#%s not found', $report->getReferenceNumber())
            );

            return;
        }

        if (null === $profitStarsTransaction = $transaction->getOrder()->getProfitStarsTransaction()) {
            $this->logger->emergency(
                sprintf('ProfitStarsTransaction for Order#%d not found', $transaction->getOrder()->getId())
            );

            return;
        }

        if (null === $transaction->getDepositDate()) {
            $transaction->setDepositDate(new \DateTime($report->getEventDateTime()));
        }

        $profitStarsTransaction->setTransactionNumber($report->getTransactionNumber());

        $this->em->flush();
    }

    /**
     * @param WSEventReport $report
     */
    protected function syncReversalReport(WSEventReport $report)
    {
        $this->logger->info(
            sprintf('Try to sync ReversalReport for TransactionId#%s', $report->getTransactionNumber())
        );
        /** @var Transaction $transaction */
        $transaction = $this->getTransactionRepository()->findOneCompletedByProfitStarsTransactionId(
            $report->getTransactionNumber()
        );
        if (null === $transaction) {
            $this->logger->info(
                sprintf(
                    'Completed Transaction with profitStars transactionId#%s not found.',
                    $report->getTransactionNumber()
                )
            );

            return;
        }

        $order = $transaction->getOrder();
        if ($order->getStatus() === OrderStatus::COMPLETE || $order->getStatus() === OrderStatus::PENDING) {
            $this->createReversalTransaction($order, $report);
        } elseif (true === in_array($order->getStatus(), $this->reversedOrderStatuses)) {
            $this->logger->info(sprintf('Order#%d is already reversed, skip', $order->getId()));

            return;
        } else {
            $this->logger->alert(sprintf('Unexpected status "%s" for Order#%d', $order->getStatus(), $order->getId()));

            return;
        }
    }

    /**
     * @param Order         $order
     * @param WSEventReport $report
     *
     * @return Transaction
     */
    protected function createReversalTransaction(Order $order, WSEventReport $report)
    {
        $this->logger->info(sprintf(
            'Creating reversed transaction for Order#%d, Transaction#%d.',
            $order->getId(),
            $report->getTransactionNumber()
        ));
        $transaction = new Transaction();
        $transaction->setTransactionId($report->getReferenceNumber());
        $transaction->setDepositDate(new \DateTime($report->getEventDateTime()));
        $transaction->setOrder($order);
        $transaction->setAmount($report->getTotalAmount());
        $transaction->setIsSuccessful(true);
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setMessages(sprintf('%s %s', $report->getEventDatastring(), $report->getReturnCode()));

        $this->em->persist($transaction);
        $this->em->flush();

        switch ($report->getEventType()) {
            case WSTransactionEvent::RETURNED_NSF:
            case WSTransactionEvent::OTHER_CHECK21_RETURNS:
            case WSTransactionEvent::DISPUTED:
            case WSTransactionEvent::RETURNED_BAD_ACCOUNT:
            case WSTransactionEvent::DECLINED:
            case WSTransactionEvent::PROCESSING_ERROR:
            case WSTransactionEvent::REVERSED:
            case WSTransactionEvent::NOTICE_OF_CHANGE:
                $this->statusManager->setReturned($order);
                break;
            case WSTransactionEvent::VOIDED:
                $this->statusManager->setCancelled($order);
                break;
            case WSTransactionEvent::REFUNDED:
                $this->statusManager->setRefunded($order);
                break;
            default:
                throw new \LogicException('Unexpected status ' . $report->getEventType());
        }
    }

    /**
     * @param string    $locationId
     * @param string    $entityId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return WSEventReport[]
     */
    protected function getReports($locationId, $entityId, \DateTime $startDate, \DateTime $endDate)
    {
        $locations = new ArrayOfInt();
        $locations->setInt([$locationId]);

        $response = $this->client->GetHistoricalEventReport(
            $this->storeId,
            $this->storeKey,
            $entityId,
            $this->getArrayOfWSDisplayFields(),
            $locations,
            WSTransactionEvent::__NONE,
            $this->getArrayOfWSTransactionStatus(),
            $this->getArrayOfWSSettlementType(),
            WSPaymentType::__NONE,
            $this->getArrayOfWSPaymentOrigin(),
            $this->getArrayOfWSSettlementStatus(),
            WSAuthResponseCode::__NONE,
            WSOperationType::__NONE,
            $startDate->format('Y-m-d\TH:i:s'),
            $endDate->format('Y-m-d\TH:i:s'),
            0,
            99999
        );

        if (null === $eventReports = $response->getGetHistoricalEventReportResult()->getWSEventReport()) {
            $this->logger->info(
                'ProfitStars returned empty response',
                [
                    'locationId' => $locationId,
                    'entityId' => $entityId,
                    'date' => $startDate->format('Y-m-d')
                ]
            );

            return [];
        }

        $reports = [];
        foreach ($eventReports as $report) {
            $reports[] = $report;
        }

        return $reports;
    }

    /**
     * @return ArrayOfWSDisplayFields
     */
    protected function getArrayOfWSDisplayFields()
    {
        $additionalFields = [
            WSDisplayFields::TRANSACTION_STATUS_NAME,
            WSDisplayFields::PAYMENT_TYPE_NAME,
            WSDisplayFields::NAME_ON_ACCOUNT,
            WSDisplayFields::TRANSACTION_NUMBER,
            WSDisplayFields::REFERENCE_NUMBER,
            WSDisplayFields::CUSTOMER_NUMBER,
            WSDisplayFields::OPERATION_TYPE_NAME,
            WSDisplayFields::LOCATION_DISPLAY_NAME,
            WSDisplayFields::TOTAL_AMOUNT,
            WSDisplayFields::AUTH_RESPONSE_TYPE_NAME,
            WSDisplayFields::PAYMENT_ORIGIN_NAME,
            WSDisplayFields::SETTLEMENT_STATUS_NAME,
            WSDisplayFields::DISPLAY_ACCOUNT_NUMBER,
            WSDisplayFields::CHECK_NUMBER,
            WSDisplayFields::THIRD_PARTY_REFERENCE_NUMBER,
            WSDisplayFields::AUDIT_USER_NAME,
            WSDisplayFields::EVENT_DATETIME,
            WSDisplayFields::EVENT_TYPE_NAME,
            WSDisplayFields::EVENT_DATASTRING,
            WSDisplayFields::OWNERAPPLICATION,
            WSDisplayFields::RECEIVINGAPPLICATION,
            WSDisplayFields::OWNERAPPREFERENCEID,
            WSDisplayFields::RETURNCODE,
            WSDisplayFields::NOTICE_OF_CHANGE,
            WSDisplayFields::SEQUENCEID,
            WSDisplayFields::BATCHNUMBER,
            WSDisplayFields::ORIGINATEDAS,
            WSDisplayFields::ISDUPLICATE,
            WSDisplayFields::EFFECTIVEDATE,
            WSDisplayFields::FACEFEETYPE,
        ];
        $arrayOfWSDisplayFields = new ArrayOfWSDisplayFields();
        $arrayOfWSDisplayFields->setWSDisplayFields($additionalFields);

        return $arrayOfWSDisplayFields;
    }

    /**
     * @return ArrayOfWSTransactionStatus
     */
    protected function getArrayOfWSTransactionStatus()
    {
        $statuses = [
            WSTransactionStatus::__NONE,
        ];
        $arrayOfWSTransactionStatus = new ArrayOfWSTransactionStatus();
        $arrayOfWSTransactionStatus->setWSTransactionStatus($statuses);

        return $arrayOfWSTransactionStatus;
    }

    /**
     * @return ArrayOfWSSettlementType
     */
    protected function getArrayOfWSSettlementType()
    {
        $types = [
            WSSettlementType::NONE,
        ];
        $arrayOfWSSettlementType = new ArrayOfWSSettlementType();
        $arrayOfWSSettlementType->setWSSettlementType($types);

        return $arrayOfWSSettlementType;
    }

    /**
     * @return ArrayOfWSPaymentOrigin
     */
    protected function getArrayOfWSPaymentOrigin()
    {
        $paymentOrigins = [
            WSPaymentOrigin::__NONE,
        ];
        $arrayOfWSPaymentOrigin = new ArrayOfWSPaymentOrigin();
        $arrayOfWSPaymentOrigin->setWSPaymentOrigin($paymentOrigins);

        return $arrayOfWSPaymentOrigin;
    }

    /**
     * @return ArrayOfWSSettlementStatus
     */
    protected function getArrayOfWSSettlementStatus()
    {
        $statuses = [
            WSSettlementStatus::__NONE,
        ];
        $arrayOfWSSettlementStatus = new ArrayOfWSSettlementStatus();
        $arrayOfWSSettlementStatus->setWSSettlementStatus($statuses);

        return $arrayOfWSSettlementStatus;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\TransactionRepository
     */
    protected function getTransactionRepository()
    {
        return $this->em->getRepository('RjDataBundle:Transaction');
    }
}
