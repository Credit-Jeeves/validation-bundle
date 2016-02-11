<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\MRI;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Tests\Services\ContractSynchronizerTestBase as Base;

class MRIContractSynchronizerCase extends Base
{
    /**
     * @test
     */
    public function shouldSyncContractsBalanceForMRI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'Should have contract with id 20 in fixtures');
        /** @var Contract $contract2 */
        $contract2 = $repo->find(9);
        $this->assertNotNull($contract2, 'Should have contract with id 9 in fixtures');
        $this->assertEquals(
            0,
            $contract->getIntegratedBalance(),
            'Integrated balance for contract with id 20 should be 0'
        );
        $this->assertEquals(
            0,
            $contract2->getIntegratedBalance(),
            'Integrated balance for contract with id 9 should be 0'
        );
        $contract->setPaymentAccepted(null);
        $contract2->setPaymentAccepted(null);
        $contract->getHolding()->setAccountingSystem(AccountingSystem::MRI);
        $contract2->getHolding()->setAccountingSystem(AccountingSystem::MRI);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping2 = $contract2->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(MRIClientCase::PROPERTY_ID);
        $propertyMapping2->setExternalPropertyId(MRIClientCase::PROPERTY_ID);
        $unit = $contract->getUnit();
        $unit2 = $contract2->getUnit();
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('500|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit2->getUnitMapping()->setExternalUnitId('500|01|101');
        $unit->setUnitMapping($unitExternalMapping);
        $unit->setName('101');
        $unit2->setName('101');
        $tenant = $contract->getTenant();
        $tenant2 = $contract2->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId('0000000091');
        $residentMapping2 = $tenant2->getResidentForHolding($contract->getHolding());
        $residentMapping2->setResidentId('0000000091');
        $em->flush();

        $balanceSynchronizer = $this->getContainer()->get('mri.contract_sync');
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

        $updatedContract = $repo->find($contract->getId());
        $updatedContract2 = $repo->find($contract2->getId());
        $this->assertGreaterThan(
            8340,
            (int) $updatedContract->getIntegratedBalance(),
            'Balance not updated for first contract'
        );
        $this->assertEquals(
            0,
            $updatedContract->getPaymentAccepted(),
            'PaymentAccepted should be set for first contract'
        );
        $this->assertGreaterThan(
            8340,
            (int) $updatedContract2->getIntegratedBalance(),
            'Balance not updated for second contract'
        );
        $this->assertEquals(
            0,
            $updatedContract2->getPaymentAccepted(),
            'PaymentAccepted should be set for second contract'
        );
    }

    /**
     * @test
     */
    public function shouldSyncContracWaitingBalanceForMRI()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $repositoryContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting');
        /** @var ContractWaiting $contractWaiting */
        $contractWaiting = $repositoryContractWaiting->findOneBy(['residentId' => 't0013535']);
        $this->assertNotNull($contractWaiting, 'We should find contract waiting with resident t0013535');
        $this->assertEquals(0, $contractWaiting->getIntegratedBalance(), 'Balance should be 0');
        $contractWaiting->setPaymentAccepted(null);
        $contractWaiting->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::MRI);
        $propertyMapping = $contractWaiting->getProperty()->getPropertyMappingByHolding(
            $contractWaiting->getGroup()->getHolding()
        );
        $propertyMapping->setExternalPropertyId(MRIClientCase::PROPERTY_ID);
        $unit = $contractWaiting->getUnit();
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('500|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit->setUnitMapping($unitExternalMapping);
        $unit->setName('101');
        $contractWaiting->setResidentId('0000000091');

        $em->flush();

        $balanceSynchronizer = $this->getContainer()->get('mri.contract_sync');
        $balanceSynchronizer->syncBalance();

        $externalPropertyId = $contractWaiting
            ->getProperty()
            ->getPropertyMappingByHolding($contractWaiting->getGroup()->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertBalanceSyncJob($lastJob, $contractWaiting->getGroup()->getHolding(), $externalPropertyId);

        $this->runSyncBalanceCommand($contractWaiting->getGroup()->getHolding(), $externalPropertyId);

        /** @var ContractWaiting $updatedContractWaiting */
        $updatedContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($contractWaiting->getId());
        $this->assertGreaterThan(8340, (int) $updatedContractWaiting->getIntegratedBalance(), 'Balance not updated');
        $this->assertEquals(0, $updatedContractWaiting->getPaymentAccepted(), 'PaymentAccepted should be set');
    }

    /**
     * @test
     */
    public function shouldSyncContractRentForMRI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'Should have contract in fixtures');
        $contract->setRent(0);
        $contract->getHolding()->setAccountingSystem(AccountingSystem::MRI);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->getHolding()->setRecurringCodes('RNT, YY');
        $propertyMapping->setExternalPropertyId(MRIClientCase::PROPERTY_ID);
        $unit = $contract->getUnit();
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('500|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit->setUnitMapping($unitExternalMapping);
        $unit->setName('101');
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId('0000000091');
        $em->flush();

        $balanceSynchronizer = $this->getContainer()->get('mri.contract_sync');
        $balanceSynchronizer->syncRent();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertRentSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncRentCommand($contract->getHolding(), $externalPropertyId);

        $updatedContract = $repo->find($contract->getId());
        $this->assertGreaterThan(0, (int) $updatedContract->getRent(), 'Rent not updated');
    }

    /**
     * @test
     */
    public function shouldSyncContracWaitingRentForMRI()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $repositoryContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting');
        /** @var ContractWaiting $contractWaiting */
        $contractWaiting = $repositoryContractWaiting->findOneBy(['residentId' => 't0013535']);
        $this->assertNotNull($contractWaiting, 'We should find contract waiting with resident t0013535');
        $contractWaiting->setRent(0);
        $contractWaiting->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::MRI);
        $contractWaiting->getGroup()->getHolding()->setUseRecurringCharges(true);
        $contractWaiting->getGroup()->getHolding()->setRecurringCodes('RNT, YY');
        $propertyMapping = $contractWaiting->getProperty()->getPropertyMappingByHolding(
            $contractWaiting->getGroup()->getHolding()
        );
        $propertyMapping->setExternalPropertyId(MRIClientCase::PROPERTY_ID);
        $unit = $contractWaiting->getUnit();
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('500|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit->setUnitMapping($unitExternalMapping);
        $unit->setName('101');
        $contractWaiting->setResidentId('0000000091');

        $em->flush();

        $balanceSynchronizer = $this->getContainer()->get('mri.contract_sync');
        $balanceSynchronizer->syncRent();

        $externalPropertyId = $contractWaiting
            ->getProperty()
            ->getPropertyMappingByHolding($contractWaiting->getGroup()->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertRentSyncJob($lastJob, $contractWaiting->getGroup()->getHolding(), $externalPropertyId);

        $this->runSyncRentCommand($contractWaiting->getGroup()->getHolding(), $externalPropertyId);

        /** @var ContractWaiting $updatedContractWaiting */
        $updatedContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($contractWaiting->getId());
        $this->assertGreaterThan(0, (int) $updatedContractWaiting->getRent(), 'Balance not updated');
    }
}
