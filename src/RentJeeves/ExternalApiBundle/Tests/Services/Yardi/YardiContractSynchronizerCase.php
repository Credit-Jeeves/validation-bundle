<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\ExternalApiBundle\Tests\Services\ContractSynchronizerTestBase as Base;

class YardiContractSynchronizerCase extends Base
{
    const PROPERTY_ID = 'rnttrk01';

    /**
     * @param Unit $unit
     */
    protected function createUnitMapping(Unit $unit)
    {
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId(sprintf('%s||%s', self::PROPERTY_ID, $unit->getActualName()));
        $unit->setUnitMapping($unitMapping);
    }
    /**
     * @test
     */
    public function shouldSyncContractBalance()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->createUnitMapping($contract->getUnit());
        $this->getEntityManager()->flush();
        $this->assertEquals(
            0,
            $contract->getIntegratedBalance(),
            'Contract in fixture should have 9 integrated balance.'
        );

        $balanceSynchronizer= $this->getContainer()->get('yardi.contract_sync');
        $balanceSynchronizer->syncBalance();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertBalanceSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncBalanceCommand($contract->getHolding(), $externalPropertyId);

        $updatedContract = $repo->find(20);
        $this->assertGreaterThan(
            4360.5,
            $updatedContract->getIntegratedBalance(),
            'We did not update integrated balance for contract.'
        );
    }

    /**
     * @test
     */
    public function shouldSyncContractWaitingBalance()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'We should have contract in fixture.');
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);
        $this->createUnitMapping($contract->getUnit());
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
        $em->flush();

        $this->assertEquals(
            0,
            $contractWaiting->getIntegratedBalance(),
            'Integrated balance for Contract Waiting should be 0. Because this is new.'
        );

        $balanceSynchronizer = $this->getContainer()->get('yardi.contract_sync');
        $balanceSynchronizer->syncBalance();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertBalanceSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncBalanceCommand($contract->getHolding(), $externalPropertyId);

        $repo = $em->getRepository('RjDataBundle:ContractWaiting');
        $updatedContractWaiting = $repo->findByHoldingPropertyUnitResident(
            $contract->getGroup()->getHolding(),
            $contract->getProperty(),
            $contract->getUnit()->getName(),
            't0011984'
        );
        $this->assertNotNull($updatedContractWaiting, 'We should find contract waiting which we just created');
        $this->assertGreaterThan(
            4360.5,
            $updatedContractWaiting->getIntegratedBalance(),
            'We did not update integrated balance for contract waiting'
        );
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
        $this->assertNotNull($contract, 'We didn\'t find contract in fixtures.');
        $this->assertEquals(850.00, $contract->getRent(), 'Contract rent in fixtures is wrong.');
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($holding = $contract->getHolding());
        $this->assertNotEmpty($residentMapping, 'Wrong fixtures, resident mapping didn\'t find');
        $residentMapping->setResidentId('t0012027');
        $contract->setExternalLeaseId('t0012027');
        $em->persist($residentMapping);
        $em->persist($contract);
        $unit = $contract->getUnit();
        $unit->setName('101');
        $this->createUnitMapping($unit);

        $holding->setUseRecurringCharges(true);
        $holding->setRecurringCodes('.rent,sss');
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $em->flush();

        $contractSynchronizer = $this->getContainer()->get('yardi.contract_sync');
        $contractSynchronizer->syncRent();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertRentSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncRentCommand($contract->getHolding(), $externalPropertyId);

        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'Did not find contract which should be updated');
        $this->assertEquals(900.00, $contract->getRent(), 'Rent didn\'t updated');
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
        $this->assertNotNull($contract, 'We didn\'t find contract in fixtures.');
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);
        $unit = $contract->getUnit();
        $unit->setName('101');
        $this->createUnitMapping($unit);
        $contractWaiting = new ContractWaiting();
        $today = new \DateTime();
        $contractWaiting->setGroup($contract->getGroup());
        $contractWaiting->setProperty($contract->getProperty());
        $contractWaiting->setUnit($contract->getUnit());
        $contractWaiting->setRent($contract->getRent());
        $contractWaiting->setResidentId('t0012027');
        $contractWaiting->setExternalLeaseId('t0012027');
        $contractWaiting->setStartAt($today);
        $contractWaiting->setFinishAt($today);
        $contractWaiting->setFirstName('Papa');
        $contractWaiting->setLastName('Karlo');
        $em->persist($contractWaiting);
        $em->flush();

        $this->assertEquals(
            850.00,
            $contractWaiting->getRent(),
            'Contract rent in fixtures is wrong.'
        );

        $holding = $contractWaiting->getGroup()->getHolding();
        $holding->setUseRecurringCharges(true);
        $holding->setRecurringCodes('.rent,sss');
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $em->flush();

        $contractSyncronizer = $this->getContainer()->get('yardi.contract_sync');
        $contractSyncronizer->syncRent();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertRentSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncRentCommand($contract->getHolding(), $externalPropertyId);

        /** @var Contract $contract */
        $this->assertEquals(
            900.00,
            $contractWaiting->getRent(),
            'Rent contract waiting didn\'t update'
        );
    }
}
