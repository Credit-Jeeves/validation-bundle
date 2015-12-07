<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services;

use RentJeeves\ExternalApiBundle\Services\ContractSynchronizerFactory;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ContractSynchronizerFactoryCase extends Base
{
    /**
     * @test
     */
    public function test()
    {
        /** @var ContractSynchronizerFactory $factory */
        $factory = $this->getContainer()->get('contract_sync.factory');
        $factory->getSynchronizer('mri');
    }
}
