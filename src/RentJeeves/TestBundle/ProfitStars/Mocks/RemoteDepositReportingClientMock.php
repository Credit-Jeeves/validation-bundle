<?php

namespace RentJeeves\TestBundle\ProfitStars\Mocks;

use RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSRemoteDepositBatch;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSRemoteDepositItem;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\GetBatchesByDateRangeResponse;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\GetItemsByBatchNumberResponse;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositBatch;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositItem;

class RemoteDepositReportingClientMock
{
    /**
     * @return GetBatchesByDateRangeResponse
     */
    public static function getMockForGetBatchesByDateRange()
    {
        $batch1 = new WSRemoteDepositBatch();
        $batch1
            ->setBatchNumber('B-111')
            ->setBatchStatus(WSBatchStatus::SENTTOTRANSACTIONPROCESSING)
            ->setCreateDateTime('2016-01-01');

        $batch2 = new WSRemoteDepositBatch();
        $batch2
            ->setBatchNumber('B-222')
            ->setBatchStatus(WSBatchStatus::OPEN)
            ->setCreateDateTime('2016-01-01');

        $batch3 = new WSRemoteDepositBatch();
        $batch3
            ->setBatchNumber('B-333')
            ->setBatchStatus(WSBatchStatus::CLOSED)
            ->setCreateDateTime('2016-01-01');

        $batches = new ArrayOfWSRemoteDepositBatch();
        $batches->setWSRemoteDepositBatch([$batch1, $batch2, $batch3]);

        $response = new GetBatchesByDateRangeResponse();
        $response->setGetBatchesByDateRangeResult($batches);

        return $response;
    }

    /**
     * @return GetItemsByBatchNumberResponse
     */
    public static function getMockForGetItemsByBatchNumber()
    {
        $encoder = new Skip32IdEncoder();
        $item1 = new WSRemoteDepositItem();
        $item1
            ->setItemId(1001)
            ->setItemDateTime('2016-01-01')
            ->setTotalAmount(1000)
            ->setCheckNumber('CH-01')
            ->setCustomerNumber($encoder->encode(2))
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
            ->setReferenceNumber('ref-01')
            ->setBatchNumber('B-111');

        $item2 = new WSRemoteDepositItem();
        $item2
            ->setItemId(1002)
            ->setItemDateTime('2016-01-01')
            ->setTotalAmount(3000)
            ->setCheckNumber('CH-02')
            ->setCustomerNumber($encoder->encode(2))
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
            ->setReferenceNumber('ref-02')
            ->setBatchNumber('B-111');

        $items = new ArrayOfWSRemoteDepositItem();
        $items->setWSRemoteDepositItem([$item1, $item2]);
        $response = new GetItemsByBatchNumberResponse();
        $response->setGetItemsByBatchNumberResult($items);

        return $response;
    }
}
