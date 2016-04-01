<?php

namespace RentJeeves\TestBundle\ProfitStars;

use RentJeeves\TestBundle\ProfitStars\Mocks\TransactionReportingClientMock;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfInt;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSDisplayFields;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSPaymentOrigin;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSSettlementStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSSettlementType;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSTransactionStatus;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\TransactionReportingClient as Base;

class TransactionReportingClient extends Base
{

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function GetHistoricalEventReport(
        $storeId,
        $storeKey,
        $entityId,
        ArrayOfWSDisplayFields $wsdisplayFields,
        ArrayOfInt $locationIds,
        $wstransEvent,
        ArrayOfWSTransactionStatus $wstransStatus,
        ArrayOfWSSettlementType $wssettlementType,
        $wspaymentType,
        ArrayOfWSPaymentOrigin $wspaymentOrigin,
        ArrayOfWSSettlementStatus $wssettlementStatus,
        $wsauthResponseCode,
        $wsopType,
        $beginTransDate,
        $endTransDate,
        $fromAmount,
        $toAmount
    ) {
        $a = 1;

        return TransactionReportingClientMock::getMockForGetHistoricalEventReport();
    }
}
