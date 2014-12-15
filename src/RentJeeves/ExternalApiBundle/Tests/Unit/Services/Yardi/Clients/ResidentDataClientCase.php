<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentDataResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileTenant;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileUnit;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\YardiClientEnum;
use RentJeeves\ExternalApiBundle\Tests\Unit\Services\Yardi\Clients\BaseClientCase as Base;

class ResidentDataClientCase extends Base
{
    /**
     * @test
     */
    public function getResidents()
    {
        $container = $this->getKernel()->getContainer();
        $clientFactory = $container->get('soap.client.factory');

        $residentClient = $clientFactory->getClient(
            $this->getYardiSettings(),
            YardiClientEnum::RESIDENT_DATA
        );

        $result = $residentClient->getResidents('rnttrk01');

        $this->assertTrue($result instanceof GetResidentsResponse);
        $this->assertTrue(count($result->getPropertyResidents()->getResidents()->getResidents()) > 0);
    }

    /**
     * @test
     */
    public function getResidentData()
    {
        $container = $this->getKernel()->getContainer();
        $clientFactory = $container->get('soap.client.factory');

        $residentClient = $clientFactory->getClient(
            $this->getYardiSettings(),
            YardiClientEnum::RESIDENT_DATA
        );

        $result = $residentClient->getResidentData('rnttrk01', 't0012027');

        $this->assertTrue($result instanceof GetResidentDataResponse);
        $this->assertTrue($result->getLeaseFiles()->getLeaseFile() instanceof ResidentLeaseFile);
        $this->assertTrue($result->getLeaseFiles()->getLeaseFile()->getUnit() instanceof LeaseFileUnit);
        $this->assertTrue($result->getLeaseFiles()->getLeaseFile()->getTenantDetails() instanceof LeaseFileTenant);
    }
}
