<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSBatchEventType;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\RemoteDepositReportingClient;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSBatchEventType;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositBatch;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositItem;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfInt;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSDisplayFields;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSPaymentOrigin;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSSettlementStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSSettlementType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSTransactionStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\TransactionReportingClient;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSAuthResponseCode;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSDisplayFields;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSOperationType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSPaymentOrigin;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSPaymentType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSSettlementStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSSettlementType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSTransactionEvent;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSTransactionStatus;

/**
 * Service name "payment_processor.profit_stars.rdc.report_loader"
 */
class ReportLoader
{
    /** @var TransactionReportingClient */
    protected $transactionReportingClient;

    /** @var RDCClient */
    protected $remoteDepositReportingClient;

    /** @var ScannedCheckTransformer */
    protected $checkTransformer;

    /** @var EntityManager */
    protected $em;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $storeId;

    /** @var string */
    protected $storeKey;

    public function __construct(
        TransactionReportingClient $transactionReportingClient,
        RDCClient $depositReportingClient,
        ScannedCheckTransformer $checkTransformer,
        EntityManager $em,
        LoggerInterface $logger,
        $rentTrackStoreId,
        $rentTrackStoreKey
    ) {
        $this->transactionReportingClient = $transactionReportingClient;
        $this->remoteDepositReportingClient = $depositReportingClient;
        $this->checkTransformer = $checkTransformer;
        $this->em = $em;
        $this->logger = $logger;
        $this->storeId = $rentTrackStoreId;
        $this->storeKey = $rentTrackStoreKey;
    }

    /**
     *  Don't review! Will be finished next sprint.
     *
     * @return PaymentProcessorReport
     */
    public function loadReport()
    {
        $report = new PaymentProcessorReport();

        $date = new \DateTime();

        $depositTransactions = $this->loadDepositReport($date);
        $reversedTransactions = [];

        $report->setTransactions(array_merge($depositTransactions, $reversedTransactions));

        return $report;
    }

    /**
     * Uses GetItemsByBatchNumber
     *
     * @param \DateTime $date
     * @throws \RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException
     */
    protected function loadDepositReport(\DateTime $date)
    {
        $this->logger->info(sprintf('Loading deposited checks for date "%s"', $date->format('m-d-Y')));

        $result = [];
        $groups = $this->em->getRepository('DataBundle:Group')->getProfitStarsEnabledGroups();
        /** @var Group $group */
        foreach ($groups as $group) {
            $this->logger->info(sprintf(
                'Loading deposited checks for group#%d, date "%s"',
                $group->getId(),
                $date->format('m-d-Y')
            ));

            try {
                $batches = $this->getBatches($group, $date);
                foreach ($batches as $batch) {
                    $batchNumber = $batch->getBatchNumber();
                    $this->logger->info(sprintf(
                        'Processing batch with number: %s and status: %s',
                        $batchNumber,
                        $batch->getBatchStatus()
                    ));

                    $batchItems = $this->getBatchItems($group, $batchNumber);
                    foreach ($batchItems as $orderData) {
                        $result[] = $this->checkTransformer->transformToDepositReportTransaction($orderData);
                    }
                }
            } catch (ProfitStarsException $e) {
                $this->logger->alert(sprintf(
                    'A problem occurred while loading ProfitStars checks for Group#%d, date "%s": %s',
                    $group->getId(),
                    $date->format('m-d-Y'),
                    $e->getMessage()
                ));
            }
        }

        return $result;
    }


    /**
     * @param Group $group
     * @param \DateTime $date
     * @return WSRemoteDepositBatch[]
     * @throws ProfitStarsException
     */
    protected function getBatches(Group $group, \DateTime $date)
    {
        // what batch statuses should we take here?
        return $this->remoteDepositReportingClient->getBatches(
            $group,
            $date,
            [
//                WSBatchStatus::OPEN,
//                WSBatchStatus::CLOSED,
//                WSBatchStatus::DELETED,
//                WSBatchStatus::ERROR,
//                WSBatchStatus::NEEDSBALANCING,
                WSBatchStatus::PARTIALDEPOSIT,
//                WSBatchStatus::READYFORPROCESSING,
                WSBatchStatus::PARTIALLYPROCESSED,
//                WSBatchStatus::REJECTED,
                WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
//                WSBatchStatus::TPERROR,
//                WSBatchStatus::TPBATCHCREATIONFAILED
            ]
        );
    }

    /**
     * @param Group $group
     * @param string $batchNumber
     * @return WSRemoteDepositItem[]
     * @throws ProfitStarsException
     */
    protected function getBatchItems(Group $group, $batchNumber)
    {
        // what item statuses should we take here?
        return $this->remoteDepositReportingClient->getBatchItems(
            $group,
            $batchNumber,
            [
//                WSItemStatus::CREATED,
                WSItemStatus::APPROVED,
//                WSItemStatus::CLOSED,
//                WSItemStatus::DELETED,
                WSItemStatus::NEEDSATTENTION,
//                WSItemStatus::REJECTED,
                WSItemStatus::NEEDSRESCAN,
                WSItemStatus::RELEASED,
                WSItemStatus::RESCANNED,
                WSItemStatus::RESOLVED,
                WSItemStatus::SENTTOTRANSACTIONPROCESSING,
//                WSItemStatus::CHECKDECISIONINGERROR,
//                WSItemStatus::ERROR
            ]
        );
    }
}
