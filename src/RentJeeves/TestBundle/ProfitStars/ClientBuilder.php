<?php

namespace RentJeeves\TestBundle\ProfitStars;

use Psr\Log\LoggerInterface;
use RentTrack\ProfitStarsClientBundle\Client\ClientBuilder as Base;

class ClientBuilder extends Base
{
    /**
     * @param LoggerInterface $logger
     * @param bool $isDebug
     * @return TransactionReportingClient
     */
    public static function buildTransactionReportingClient(LoggerInterface $logger, $isDebug = false)
    {
        return new TransactionReportingClient();
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $isDebug
     * @return RemoteDepositReportingClient
     */
    public static function buildRemoteDepositReportingClient(LoggerInterface $logger, $isDebug = false)
    {
        return new RemoteDepositReportingClient();
    }
}
