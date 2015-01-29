<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\YardiClientEnum;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\BaseClientCase as Base;

class ResidentTransactionsClientCase extends Base
{
    /**
     * @return ResidentTransactionsClient
     */
    protected function getClient()
    {
        $container = $this->getKernel()->getContainer();
        $clientFactory = $container->get('soap.client.factory');

        return $clientFactory->getClient(
            $this->getYardiSettings(),
            YardiClientEnum::RESIDENT_TRANSACTIONS
        );
    }

    /**
     * @test
     */
    public function getPropertyConfigurations()
    {
        $client = $this->getClient();
        $result = $client->getPropertyConfigurations();

        $this->assertTrue($result instanceof GetPropertyConfigurationsResponse);
    }

    /**
     * @test
     */
    public function getResidentTransactions()
    {
        $client = $this->getClient();
        /**
         * @var $response GetResidentTransactionsLoginResponse
         */
        $response = $client->getResidentTransactions('rnttrk01');
        $this->assertTrue($response instanceof GetResidentTransactionsLoginResponse);
    }
}
