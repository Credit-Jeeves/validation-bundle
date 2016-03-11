<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\TransactionStatus;
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
     */
    public function loadScannedChecks(Group $group, \DateTime $date)
    {
        $this->logger->info(sprintf(
            'Loading scanned check for Group#%d, date "%s"',
            $group->getId(),
            $date->format('m-d-Y')
        ));
        $countChecks = 0;
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

                $batchItems = $this->getBatchItems($group, $batchNumber);
                $this->logger->info(sprintf('Batch %s has %d items', $batch->getBatchNumber(), count($batchItems)));
                foreach ($batchItems as $orderData) {
                    $isCreatedNewOrder = $this->createOrderIfItIsNew($orderData);
                    $countChecks = true === $isCreatedNewOrder ? $countChecks + 1 : $countChecks;
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

        return $countChecks;
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
                WSBatchStatus::CLOSED,
                WSBatchStatus::READYFORPROCESSING,
                WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
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
            ]
        );
    }

    /**
     * @param WSRemoteDepositItem $depositItem
     * @return null|Transaction
     */
    protected function getExistingTransaction(WSRemoteDepositItem $depositItem)
    {
        return $this->em->getRepository('RjDataBundle:Transaction')->findOneBy([
            'transactionId' => $depositItem->getReferenceNumber(),
            'batchId' => $depositItem->getBatchNumber(),
            'status' => TransactionStatus::COMPLETE
        ]);
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
                'Transaction already exists for referenceNumber "%s", batchNumber "%s" -- skipping item',
                $depositItem->getReferenceNumber(),
                $depositItem->getBatchNumber()
            ));
        }

        return false;
    }
}
