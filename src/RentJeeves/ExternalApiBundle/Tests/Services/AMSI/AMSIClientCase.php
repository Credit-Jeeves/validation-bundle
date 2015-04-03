<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use RentJeeves\ExternalApiBundle\Model\AMSI\OpenItem;
use RentJeeves\ExternalApiBundle\Services\AMSI\AMSIClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class AMSIClientCase extends Base
{
    const EXTERNAL_PROPERTY_ID = '001';

    public $settings;

    /**
     * Before a test method is run, a template method called setUp() is invoked.
     * setUp() is where you create the objects against which you will test.
     */
    public function setUp()
    {
        $em = $this->getEntityManager();
        $this->settings = $em->getRepository('RjDataBundle:AMSISettings')->findOneBy(
            ['user' => 'RentTrack']
        );
    }

    /**
     * @test
     */
    public function shouldCreateAmsiClient()
    {
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\AMSI\AMSIClient',
            $this->getClient()
        );
    }

    /**
     * @test
     */
    public function shouldGetPropertyResidentsWithRequiredData()
    {
        $client = $this->getClient();
        $client->setDebug(false);
        $propertyResidents = $client->getPropertyResidents(self::EXTERNAL_PROPERTY_ID, 'C');
        $leases = $propertyResidents->getLease();
        $this->assertCount(50, $leases);
        /** @var Lease $lease */
        $lease = $leases[0];
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\AMSI\Lease',
            $lease
        );
        $this->assertNotEmpty($lease->getEmail());
        $address = $lease->getAddress();
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\AMSI\Address',
            $address
        );
        $occupants = $lease->getOccupants();
        $this->assertCount(2, $occupants);
        /** @var Occupant $occupant */
        $occupant = $occupants[0];
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\AMSI\Occupant',
            $occupant
        );

        $this->assertNotEmpty($occupant->getOccuFirstName());
        $openItems = $lease->getOpenItems();
        $this->assertCount(2, $openItems);
        /** @var OpenItem $openItem */
        $openItem = $openItems[0];
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\AMSI\OpenItem',
            $openItem
        );
        $this->assertNotEmpty($openItem->getOccuLastName());
    }

    /**
     * @return AMSIClient
     */
    protected function getClient()
    {
        $this->assertNotEmpty($this->settings);
        $container = $this->getKernel()->getContainer();
        $soapClientFactory = $container->get('soap.client.factory');

        $client = $soapClientFactory->getClient(
            $this->settings,
            SoapClientEnum::AMSI_CLIENT
        );

        return $client;
    }
}
