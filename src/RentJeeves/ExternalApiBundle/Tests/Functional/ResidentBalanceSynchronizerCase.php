<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ResidentBalanceSynchronizerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSyncContractBalance()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->findOneBy(array('rent' => 850, 'balance' => -250));
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());

        $balanceSyncronizer = $this->getContainer()->get('yardi.resident_balance_sync');
        $balanceSyncronizer->run();
        $updatedContract = $repo->find($contract->getId());
        $this->assertEquals(4360.5, $updatedContract->getIntegratedBalance());
    }
}
