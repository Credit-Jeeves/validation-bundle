<?php

namespace RentJeeves\TestBundle\ProfitStars;

use Psr\Log\LoggerInterface;
use RentTrack\ProfitStarsClientBundle\Client\ClientBuilder as Base;

class ClientBuilder extends Base
{
    /**
     * @return TransactionReportingClient
     */
    public static function buildTransactionReportingClient(LoggerInterface $logger, $isDebug = false)
    {
        return new TransactionReportingClient();
    }

    /**
     * @return RemoteDepositReportingClient
     */
    public static function buildRemoteDepositReportingClient(LoggerInterface $logger, $isDebug = false)
    {
        return new RemoteDepositReportingClient();
    }
}
