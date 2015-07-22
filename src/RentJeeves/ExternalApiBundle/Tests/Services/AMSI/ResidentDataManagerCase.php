<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Services\AMSI\ResidentDataManager;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ResidentDataManagerCase extends Base
{
    /**
     * @test
     */
    public function shouldGetAmsiResidents()
    {
        $em = $this->getEntityManager();
        $settings = $em->getRepository('RjDataBundle:AMSISettings')->findOneBy(
            ['user' => 'renttrack']
        );
        /** @var ResidentDataManager $residentDataManager */
        $residentDataManager = $this->getContainer()->get('amsi.resident_data');
        $residentDataManager->setSettings($settings);
        $leases = $residentDataManager->getResidents(AMSIClientCase::EXTERNAL_PROPERTY_ID);
        $this->assertCount(44, $leases);
        /** @var Lease $lease */
        $lease = $leases[0];
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\AMSI\Lease',
            $lease
        );
    }
}
