<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentDataResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileTenant;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileUnit;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\ClientCaseBase as Base;

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
            SoapClientEnum::YARDI_RESIDENT_DATA
        );

        $result = $residentClient->getResidents('rnttrk01');

        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentsResponse',
            $result,
            'Response from Yardi can not be mapped'
        );
        $propertyResidents = $result->getPropertyResidents();
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\PropertyResidents',
            $propertyResidents,
            'Response from Yardi doesn\'t have property residents'
        );
        $residents = $propertyResidents->getResidents();
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Residents',
            $residents,
            'Response from Yardi doesn\'t have residents in property residents'
        );
        $this->assertTrue(count($residents->getResidents()) > 0);
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
            SoapClientEnum::YARDI_RESIDENT_DATA
        );
        $resident = 't0012027';
        $residentClient->setDebug(true);
        $result = $residentClient->getResidentData('rnttrk01', $resident);

        $this->assertTrue($result instanceof GetResidentDataResponse);
        $this->assertTrue($result->getLeaseFiles()->getLeaseFile() instanceof ResidentLeaseFile);
        $this->assertTrue($result->getLeaseFiles()->getLeaseFile()->getUnit() instanceof LeaseFileUnit);
        $this->assertTrue($result->getLeaseFiles()->getLeaseFile()->getTenantDetails() instanceof LeaseFileTenant);
    }
}
