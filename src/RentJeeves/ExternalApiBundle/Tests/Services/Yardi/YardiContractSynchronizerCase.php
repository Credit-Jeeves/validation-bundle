<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
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
}
