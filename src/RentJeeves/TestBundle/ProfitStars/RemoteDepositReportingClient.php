<?php

namespace RentJeeves\TestBundle\ProfitStars;

use RentJeeves\TestBundle\ProfitStars\Mocks\RemoteDepositReportingClientMock;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSItemStatus;
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
    public function GetItemsByBatchNumber($StoreId, $StoreKey, $EntityId, $BatchNumber, ArrayOfWSItemStatus $ItemStatus)
    {
        return RemoteDepositReportingClientMock::getMockForGetItemsByBatchNumber();
    }
}
