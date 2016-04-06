<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException;
use RentJeeves\DataBundle\Entity\ProfitStarsBatch;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\ProfitStarsBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositBatch;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositItem;

/**
 * Service "payment_processor.profit_stars.rdc.remote_deposit_loader"
 */
class RemoteDepositLoader
{
    /** @var RDCClient */
    protected $client;

    /** @var ScannedCheckTransformer */
    protected $checkTransformer;

    /** @var EntityManager */
    protected $em;

    /** @var LoggerInterface */
    protected $logger;

    /** @var integer */
    protected $countChecks;

    /**
     * @param RDCClient $client
     * @param ScannedCheckTransformer $checkTransformer
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(
        RDCClient $client,
        ScannedCheckTransformer $checkTransformer,
        EntityManager $em,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->checkTransformer = $checkTransformer;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param Group $group
     * @param \DateTime $date
     * @return int
     */
    public function loadScannedChecks(Group $group, \DateTime $date)
    {
        $this->logger->info(sprintf(
            'Loading scanned check for Group#%d, date "%s"',
            $group->getId(),
            $date->format('m-d-Y')
        ));
        $this->countChecks = 0;
        try {
            $batches = $this->getBatches($group, $date);
            $this->logger->info(sprintf(
                'Got %d batches for Group#%d, date %s',
                count($batches),
                $group->getId(),
                $date->format('m-d-Y')
            ));
            foreach ($batches as $batch) {
                $batchNumber = $batch->getBatchNumber();
                $this->logger->info(sprintf(
                    'Processing batch with number: %s and status: %s',
                    $batchNumber,
                    $batch->getBatchStatus()
                ));
                if ($this->isAllowedBatchStatus($batch)) {
                    $this->processBatch($batch, $group);
                } else {
                    $this->logger->alert(sprintf(
                        'Batch "%s" has alert state "%s". Skipping.',
                        $batch->getBatchNumber(),
                        $batch->getBatchStatus()
                    ));
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

        return $this->countChecks;
    }

    /**
     * @param WSRemoteDepositBatch $batch
     * @param Group $group
     */
    protected function processBatch(WSRemoteDepositBatch $batch, Group $group)
    {
        $profitStarsBatch = $this->getProfitStarsBatch($batch->getBatchNumber());
        if (null !== $profitStarsBatch) {
            if ($profitStarsBatch->isOpen() && !$this->isSentToTransactionProcessingBatch($batch)) {
                $this->logger->emergency(
                    sprintf(
                        'ProfitStars CheckScanning batch #%s for group #%d is in unexpected state %s.',
                        $batch->getBatchNumber(),
                        $group->getId(),
                        $batch->getBatchStatus()
                    )
                );

                return;
            }
            if ($profitStarsBatch->isClosed()) {
                $this->logger->info(
                    sprintf(
                        'Skipping existing ProfitStarsBatch with status closed for batch %s and group #%d',
                        $batch->getBatchNumber(),
                        $group->getId()
                    )
                );

                return;
            }
        } else {
            $profitStarsBatch = $this->createProfitStarsBatch($batch, $group);
        }

        $batchItems = $this->getBatchItems($group, $batch->getBatchNumber());
        $this->logger->info(sprintf('Batch %s has %d items', $batch->getBatchNumber(), count($batchItems)));
        foreach ($batchItems as $batchItem) {
            if ($this->isAllowedItemStatus($batchItem)) {
                $isCreatedNewOrder = $this->createOrderIfItIsNew($batchItem);
                $this->incrementCountChecks($isCreatedNewOrder);
            } else {
                $this->logger->alert(sprintf(
                    'Item id#%s from Batch "%s" has alert state "%s". Skipping.',
                    $batchItem->getItemId(),
                    $batch->getBatchNumber(),
                    $batchItem->getItemStatus()
                ));
            }
        }

        if ($this->isSentToTransactionProcessingBatch($batch)) {
            $this->closeProfitStarsBatch($profitStarsBatch);
        }
    }

    /**
     * @param WSRemoteDepositBatch $batch
     * @return bool
     */
    protected function isAllowedBatchStatus(WSRemoteDepositBatch $batch)
    {
        $allowedStatuses = [
            WSBatchStatus::OPEN,
            WSBatchStatus::CLOSED,
            WSBatchStatus::READYFORPROCESSING,
            WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
        ];
        if (in_array($batch->getBatchStatus(), $allowedStatuses)) {
            return true;
        }

        return false;
    }

    /**
     * @param WSRemoteDepositItem $item
     * @return bool
     */
    protected function isAllowedItemStatus(WSRemoteDepositItem $item)
    {
        $allowedStatuses = [
            WSItemStatus::CREATED,
            WSItemStatus::APPROVED,
            WSItemStatus::SENTTOTRANSACTIONPROCESSING,
        ];
        if (in_array($item->getItemStatus(), $allowedStatuses)) {
            return true;
        }

        return false;
    }

    /**
     * @param Group $group
     * @param \DateTime $date
     * @return WSRemoteDepositBatch[]
     * @throws ProfitStarsException
     */
    protected function getBatches(Group $group, \DateTime $date)
    {
        return $this->client->getBatches(
            $group,
            $date,
            [
                WSBatchStatus::OPEN,
                WSBatchStatus::CLOSED,
                WSBatchStatus::ERROR,
                WSBatchStatus::READYFORPROCESSING,
                WSBatchStatus::REJECTED,
                WSBatchStatus::DELETED,
                WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                WSBatchStatus::TPERROR,
                WSBatchStatus::NEEDSBALANCING,
                WSBatchStatus::PARTIALLYPROCESSED,
                WSBatchStatus::TPBATCHCREATIONFAILED,
                WSBatchStatus::PARTIALDEPOSIT
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
        return $this->client->getBatchItems(
            $group,
            $batchNumber,
            [
                WSItemStatus::CREATED,
                WSItemStatus::APPROVED,
                WSItemStatus::SENTTOTRANSACTIONPROCESSING,
                WSItemStatus::CLOSED,
                WSItemStatus::DELETED,
                WSItemStatus::ERROR,
                WSItemStatus::CHECKDECISIONINGERROR,
                WSItemStatus::NEEDSATTENTION,
                WSItemStatus::NEEDSRESCAN,
                WSItemStatus::REJECTED,
                WSItemStatus::RELEASED,
                WSItemStatus::RESCANNED,
                WSItemStatus::TPERROR,
                WSItemStatus::RESOLVED,
                WSItemStatus::NONE
            ]
        );
    }

    /**
     * @param WSRemoteDepositItem $depositItem
     * @return null|Transaction
     */
    protected function getExistingTransaction(WSRemoteDepositItem $depositItem)
    {
        return $this->em->getRepository('RjDataBundle:Transaction')
            ->getTransactionByProfitStarsItemId($depositItem->getItemId());
    }

    /**
     * @param WSRemoteDepositItem $depositItem
     * @return bool
     */
    protected function createOrderIfItIsNew(WSRemoteDepositItem $depositItem)
    {
        if (true == $depositItem->getDeleted()) {
            $this->logger->info(sprintf(
                'Skipping DELETED item: referenceNumber "%s", batchNumber "%s", checkNumber "%s", itemStatus "%s"',
                $depositItem->getReferenceNumber(),
                $depositItem->getBatchNumber(),
                $depositItem->getCheckNumber(),
                $depositItem->getItemStatus()
            ));

            return false;
        }

        if (null === $transaction = $this->getExistingTransaction($depositItem)) {
            $this->logger->info(sprintf(
                'Adding new order: referenceNumber "%s", batchNumber "%s", checkNumber "%s", itemStatus "%s"',
                $depositItem->getReferenceNumber(),
                $depositItem->getBatchNumber(),
                $depositItem->getCheckNumber(),
                $depositItem->getItemStatus()
            ));
            try {
                $order = $this->checkTransformer->transformToOrder($depositItem);
                $this->em->persist($order);
                $this->em->flush();

                return true;
            } catch (\Exception $e) {
                $this->logger->alert(sprintf(
                    'An error occurred when trying to create new order with referenceNumber "%s", batchNumber "%s": %s',
                    $depositItem->getReferenceNumber(),
                    $depositItem->getBatchNumber(),
                    $e->getMessage()
                ));
            }
        } else {
            $this->logger->info(sprintf(
                'Transaction already exists for itemId "%s", batch#%s -- trying to set reference# as transactionId',
                $depositItem->getItemId(),
                $depositItem->getBatchNumber()
            ));

            $this->updateTransactionId($transaction, $depositItem);
        }

        return false;
    }

    /**
     * @param string $number
     * @return null|ProfitStarsBatch
     */
    protected function getProfitStarsBatch($number)
    {
        return $this->em->getRepository('RjDataBundle:ProfitStarsBatch')->findOneBy(['batchNumber' => $number]);
    }

    /**
     * @param WSRemoteDepositBatch $batch
     * @param Group $group
     * @return ProfitStarsBatch
     */
    protected function createProfitStarsBatch(WSRemoteDepositBatch $batch, Group $group)
    {
        $profitStarsBatch = new ProfitStarsBatch();
        $profitStarsBatch->setHolding($group->getHolding());
        $profitStarsBatch->setBatchNumber($batch->getBatchNumber());
        $profitStarsBatch->setStatus(ProfitStarsBatchStatus::OPEN);
        $createdAt = new \DateTime($batch->getCreateDateTime());
        $profitStarsBatch->setCreatedAt($createdAt);

        $this->em->persist($profitStarsBatch);
        $this->em->flush();

        return $profitStarsBatch;
    }

    /**
     * @param ProfitStarsBatch $batch
     */
    protected function closeProfitStarsBatch(ProfitStarsBatch $batch)
    {
        $batch->setStatus(ProfitStarsBatchStatus::CLOSED);
        $this->em->flush();
    }

    /**
     * @param WSRemoteDepositBatch $batch
     * @return bool
     */
    protected function isSentToTransactionProcessingBatch(WSRemoteDepositBatch $batch)
    {
        return $batch->getBatchStatus() === WSBatchStatus::SENTTOTRANSACTIONPROCESSING;
    }

    /**
     * @param bool $shouldIncrement
     */
    protected function incrementCountChecks($shouldIncrement)
    {
        $this->countChecks = true === $shouldIncrement ? $this->countChecks + 1 : $this->countChecks;
    }

    /**
     * @param Transaction $transaction
     * @param WSRemoteDepositItem $depositItem
     */
    protected function updateTransactionId(Transaction $transaction, WSRemoteDepositItem $depositItem)
    {
        if (false === empty($depositItem->getReferenceNumber()) && true === empty($transaction->getTransactionId())) {
            $this->logger->info(sprintf(
                'Setting referenceNumber "%s" as transactionId for Transaction #%d from Item id#%s',
                $depositItem->getReferenceNumber(),
                $transaction->getId(),
                $depositItem->getItemId()
            ));
            $transaction->setTransactionId($depositItem->getReferenceNumber());
            $this->em->flush();
        }
    }
}
