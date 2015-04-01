<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class AMSIClientCase extends Base
{
    public $settings;

    public function setUp()
    {
        $em = $this->getEntityManager();
        $this->settings = $em->getRepository('RjDataBundle:AMSISettings')->findOneBy(
            ['user' => 'RentTrack']
        );
        $this->assertNotEmpty($this->settings);
    }

    /**
     * @test
     */
    public function shouldCreateAmsiClient()
    {
        $container = $this->getKernel()->getContainer();
        $soapClientFactory = $container->get('soap.client.factory');

        $client = $soapClientFactory->getClient(
            $this->settings,
            SoapClientEnum::AMSI_CLIENT
        );

        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\AMSI\AMSIClient',
            $client
        );
    }
}
