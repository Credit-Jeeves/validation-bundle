<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ResManClientCase extends Base
{
    const PROPERTY_ID = 'MRI';

    /**
     * @test
     */
    public function shouldReturnResidents()
    {
        $container = $this->getKernel()->getContainer();
        /** @var MRIClient $mriClient */
        $mriClient = $container->get('mri.client');
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneBy(
            array(
                'name' => 'Rent Holding',
            )
        );

        $this->assertNotNull($holding);
        $mriSettings = $holding->getMriSettings();
        $this->assertNotNull($mriSettings);
        $mriClient->setSettings($mriSettings);
        $mriClient->setDebug(false);

        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\MRI\MRIClient',
            $mriClient
        );

        $response = $mriClient->getResidentTransactions(self::PROPERTY_ID);
        $response = json_decode($response, true);
        $this->assertArrayHasKey('odata.metadata', $response);
        $this->assertArrayHasKey('value', $response);
        $this->assertEmpty($response['value']);
    }
}
