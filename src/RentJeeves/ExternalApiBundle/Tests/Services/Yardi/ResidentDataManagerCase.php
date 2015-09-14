<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi;

use RentJeeves\ExternalApiBundle\Services\Yardi\ResidentDataManager;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\ClientCaseBase as Base;

class ResidentDataManagerCase extends Base
{
    /**
     * @test
     */
    public function shouldReturnCurrentAndNoticesResidents()
    {
        /** @var ResidentDataManager $residentDataManager */
        $residentDataManager = $this->getContainer()->get('yardi.resident_data');
        $propertyMapping = $this->getEntityManager()->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
            [
                'externalPropertyId' => 'rnttrk01'
            ]
        );
        $this->assertNotEmpty($propertyMapping);
        $residents = $residentDataManager->getCurrentAndNoticesResidents(
            $propertyMapping->getHolding(),
            $propertyMapping->getProperty()
        );
        $this->assertNotEmpty($residents);
    }
}
