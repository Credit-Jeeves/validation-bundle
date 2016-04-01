<?php

namespace RentJeeves\TestBundle\ProfitStars;

use RentTrack\ProfitStarsClientBundle\Client\ClientBuilder as Base;

class ClientBuilder extends Base
{
    /**
     * @return TransactionReportingClient
     */
    public static function buildTransactionReportingClient()
    {
        return new TransactionReportingClient();
    }
}
