<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class YardiContractSynchronizerCase extends Base
{
    /**
     * @test
     */
    public function shouldSyncContractBalanceForYardi()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());

        $balanceSyncronizer = $this->getContainer()->get('yardi.contract_sync');
        $balanceSyncronizer->syncBalance();
        $updatedContract = $repo->find(20);
        $this->assertGreaterThan(4360.5, $updatedContract->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function shouldSyncContractWaitingBalanceForYardi()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');

        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);

        $contractWaiting = new ContractWaiting();
        $today = new \DateTime();
        $contractWaiting->setGroup($contract->getGroup());
        $contractWaiting->setProperty($contract->getProperty());
        $contractWaiting->setUnit($contract->getUnit());
        $contractWaiting->setRent($contract->getRent());
        $contractWaiting->setResidentId('t0011984');
        $contractWaiting->setStartAt($today);
        $contractWaiting->setFinishAt($today);
        $contractWaiting->setFirstName('Papa');
        $contractWaiting->setLastName('Karlo');
        $em->persist($contractWaiting);
        $em->flush($contractWaiting);

        $this->assertEquals(0, $contractWaiting->getIntegratedBalance());

        $balanceSynchronizer = $this->getContainer()->get('yardi.contract_sync');
        $balanceSynchronizer->syncBalance();

        $repo = $em->getRepository('RjDataBundle:ContractWaiting');
        $updatedContractWaiting = $repo->findByHoldingPropertyUnitResident(
            $contract->getGroup()->getHolding(),
            $contract->getProperty(),
            $contract->getUnit()->getName(),
            't0011984'
        );
        $this->assertNotNull($updatedContractWaiting);
        $this->assertGreaterThan(4360.5, $updatedContractWaiting->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function shouldSyncContractRentForYardi()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(850.00, $contract->getRent());
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($holding = $contract->getHolding());
        $this->assertNotEmpty($residentMapping);
        $residentMapping->setResidentId('t0012027');
        $em->persist($residentMapping);
        $unit = $contract->getUnit();
        $unit->setName('101');

        $holding->setUseRecurringCharges(true);
        $holding->setRecurringCodes('.rent,sss');
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $em->flush();

        $contractSyncronizer = $this->getContainer()->get('yardi.contract_sync');
        $contractSyncronizer->syncRecurringCharge();
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(900.00, $contract->getRent());
    }

    /**
     * @test
     */
    public function shouldSyncContractWaitingRentForYardi()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertNotNull($contract);
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);
        $unit = $contract->getUnit();
        $unit->setName('101');
        $contractWaiting = new ContractWaiting();
        $today = new \DateTime();
        $contractWaiting->setGroup($contract->getGroup());
        $contractWaiting->setProperty($contract->getProperty());
        $contractWaiting->setUnit($contract->getUnit());
        $contractWaiting->setRent($contract->getRent());
        $contractWaiting->setResidentId('t0012027');
        $contractWaiting->setStartAt($today);
        $contractWaiting->setFinishAt($today);
        $contractWaiting->setFirstName('Papa');
        $contractWaiting->setLastName('Karlo');
        $em->persist($contractWaiting);
        $em->flush($contractWaiting);

        $this->assertEquals(850.00, $contractWaiting->getRent());

        $holding = $contractWaiting->getGroup()->getHolding();
        $holding->setUseRecurringCharges(true);
        $holding->setRecurringCodes('.rent,sss');
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $em->flush();

        $contractSyncronizer = $this->getContainer()->get('yardi.contract_sync');
        $contractSyncronizer->syncRecurringCharge();
        /** @var Contract $contract */
        $this->assertEquals(900.00, $contractWaiting->getRent());
    }
}
