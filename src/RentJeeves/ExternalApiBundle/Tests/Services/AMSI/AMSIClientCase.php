<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use RentJeeves\DataBundle\Entity\AMSISettings;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use RentJeeves\ExternalApiBundle\Model\AMSI\OpenItem;
use RentJeeves\ExternalApiBundle\Model\AMSI\Unit;
use RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILeasingClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class AMSIClientCase extends Base
{
    const EXTERNAL_PROPERTY_ID = '001';

    /**
     * @var AMSISettings
     */
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
            'RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILeasingClient',
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
     * @test
     */
    public function shouldGetPropertyUnits()
    {
        $client = $this->getClient();
        $client->setDebug(false);
        $units = $client->getPropertyUnits(self::EXTERNAL_PROPERTY_ID);
        $this->assertCount(64, $units);
        /** @var Unit $unit */
        $unit = $units[0];
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\AMSI\Unit',
            $unit
        );
    }

    /**
     * @return AMSILeasingClient
     */
    protected function getClient()
    {
        $this->assertNotEmpty($this->settings);
        $container = $this->getKernel()->getContainer();
        $soapClientFactory = $container->get('soap.client.factory');

        $client = $soapClientFactory->getClient(
            $this->settings,
            SoapClientEnum::AMSI_LEASING
        );

        return $client;
    }
}
