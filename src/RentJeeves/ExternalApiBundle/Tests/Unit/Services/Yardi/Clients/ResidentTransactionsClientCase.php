<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Soap\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Tests\Unit\Services\Yardi\Clients\BaseClientCase as Base;

class ResidentTransactionsClientCase extends Base
{
    /**
     * @test
     */
    public function getPropertyConfigurations()
    {
        $container = $this->getKernel()->getContainer();
        $clientFactory = $container->get('soap.client.factory');

        $resident = $clientFactory->getClient(
            $this->getYardiSettings(),
            SoapClientEnum::RESIDENT_TRANSACTIONS
        );

        $result = $resident->getPropertyConfigurations();

        $this->assertTrue($result instanceof GetPropertyConfigurationsResponse);
    }
}
