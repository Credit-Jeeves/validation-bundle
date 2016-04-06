<?php

namespace RentJeeves\TestBundle\ProfitStars;

use RentJeeves\TestBundle\ProfitStars\Mocks\RemoteDepositReportingClientMock;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfInt;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\RemoteDepositReportingClient as Base;

class RemoteDepositReportingClient extends Base
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function GetBatchesByDateRange(
        $StoreId,
        $StoreKey,
        $EntityId,
        $LocationIds,
        $StartDate,
        $EndDate,
        $BatchStatus
    ) {
        return RemoteDepositReportingClientMock::getMockForGetBatchesByDateRange();
    }

    /**
     * {@inheritdoc}
     */
    public function GetItemsByBatchNumber($storeId, $storeKey, $entityId, $batchNumber, $itemStatus)
    {
        return RemoteDepositReportingClientMock::getMockForGetItemsByBatchNumber();
    }
}
