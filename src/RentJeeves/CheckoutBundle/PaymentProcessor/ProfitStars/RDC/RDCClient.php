<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfInt;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\GetBatchesByDateRangeResponse;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\GetItemsByBatchNumberResponse;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\RemoteDepositReportingClient;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositBatch;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositItem;

/**
 * Service "payment_processor.profit_stars.rdc.client"
 */
class RDCClient
{
    /** @var RemoteDepositReportingClient */
    protected $remoteDepositReportingClient;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $storeId;

    /** @var string */
    protected $storeKey;

    /**
     * @param RemoteDepositReportingClient $depositReportingClient
     * @param LoggerInterface $logger
     * @param string $rentTrackStoreId
     * @param string $rentTrackStoreKey
     */
    public function __construct(
        RemoteDepositReportingClient $depositReportingClient,
        LoggerInterface $logger,
        $rentTrackStoreId,
        $rentTrackStoreKey
    ) {
        $this->remoteDepositReportingClient = $depositReportingClient;
        $this->logger = $logger;
        $this->storeId = $rentTrackStoreId;
        $this->storeKey = $rentTrackStoreKey;
    }

    /**
     * @param Group $group
     * @param \DateTime $date
     * @param array $statuses
     * @return WSRemoteDepositBatch[]
     * @throws ProfitStarsException
     */
    public function getBatches(Group $group, \DateTime $date, array $statuses)
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0);

        $endDate = clone $date;
        $endDate->setTime(23, 59, 59);

        $this->logger->debug(sprintf(
            'Trying to load batches for Group#%d, startDate "%s", endDate "%s".',
            $group->getId(),
            $startDate->format('Y-m-d\TH:i:s'),
            $endDate->format('Y-m-d\TH:i:s')
        ));

        $batchStatus = new ArrayOfWSBatchStatus();
        $batchStatus->setWSBatchStatus($statuses);

        $response = $this->remoteDepositReportingClient->GetBatchesByDateRange(
            $this->storeId,
            $this->storeKey,
            $group->getHolding()->getProfitStarsSettings()->getMerchantId(),
            $this->getLocations($group),
            $startDate->format('Y-m-d\TH:i:s'),
            $endDate->format('Y-m-d\TH:i:s'),
            $batchStatus
        );

        if (!$response instanceof GetBatchesByDateRangeResponse ||
            null === $response->getGetBatchesByDateRangeResult()
        ) {
            $message = sprintf(
                'GetBatchesByDateRange for group#%d and date "%s" returned empty response',
                $group->getId(),
                $date->format('Y-m-d')
            );
            throw new ProfitStarsException($message);
        }

        $result = $response->getGetBatchesByDateRangeResult()->getWSRemoteDepositBatch();
        // if there is only 1 batch, result is not array, but it is expected to be.
        if ($result instanceof WSRemoteDepositBatch) {
            return [$result];
        }

        return $result;
    }

    /**
     * @param Group $group
     * @return ArrayOfInt
     */
    protected function getLocations(Group $group)
    {
        $result = [];
        foreach ($group->getDepositAccounts() as $depositAccount) {
            if (PaymentProcessor::PROFIT_STARS === $depositAccount->getPaymentProcessor() &&
                $depositAccount->isComplete()
            ) {
                $result[] = $depositAccount->getMerchantName();
            }
        }

        $result = array_unique($result);
        if (empty($result)) {
            throw new ProfitStarsException(sprintf('Location id not found for group#%d', $group->getId()));
        }

        $locationIds = new ArrayOfInt();
        $locationIds->setInt($result);

        return $locationIds;
    }

    /**
     * @param Group $group
     * @param string $batchNumber
     * @param array $statuses
     * @return WSRemoteDepositItem[]
     * @throws ProfitStarsException
     */
    public function getBatchItems(Group $group, $batchNumber, array $statuses)
    {
        $this->logger->debug(sprintf(
            'Trying to get batch items for batch#%s, group#%d',
            $batchNumber,
            $group->getId()
        ));
        $itemStatus = new ArrayOfWSItemStatus();
        $itemStatus->setWSItemStatus($statuses);

        $response = $this->remoteDepositReportingClient->GetItemsByBatchNumber(
            $this->storeId,
            $this->storeKey,
            $group->getHolding()->getProfitStarsSettings()->getMerchantId(),
            $batchNumber,
            $itemStatus
        );
        if (!$response instanceof GetItemsByBatchNumberResponse) {
            throw new ProfitStarsException(sprintf(
                'GetItemsByBatchNumber for group#%d and batchNumber "%s" returned empty response',
                $group->getId(),
                $batchNumber
            ));
        }

        return $response->getGetItemsByBatchNumberResult()->getWSRemoteDepositItem();
    }
}
