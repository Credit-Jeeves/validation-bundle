<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Tests\Services\ContractSynchronizerTestBase as Base;

class AMSIContractSynchronizerCase extends Base
{
    /**
     * @test
     */
    public function shouldSyncContractRentForAMSI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->getHolding()->setAccountingSystem(AccountingSystem::AMSI);
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->getHolding()->setRecurringCodes('RENT');
        $contract->setRent(123321); // test value
        $contract->setExternalLeaseId(21);

        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(AMSIClientCase::EXTERNAL_PROPERTY_ID);
        $unit = $contract->getUnit();
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('001|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit->setUnitMapping($unitExternalMapping);
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId('296455');

        $em->flush();
        $em->clear();

        $balanceSynchronizer = $this->getContainer()->get('amsi.contract_sync');
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
        $this->assertEquals('1605.00', $updatedContract->getRent(), 'Rent should be updated');
    }

    /**
     * @test
     */
    public function shouldSyncContractBalanceForAMSI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $contract->getHolding()->setAccountingSystem(AccountingSystem::AMSI);
        $settings = $contract->getHolding()->getAmsiSettings();
        $settings->setSyncBalance(true);
        $contract->setExternalLeaseId(21);
        $contract->setIntegratedBalance(0);
        $unit = $contract->getUnit();
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(AMSIClientCase::EXTERNAL_PROPERTY_ID);
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('001|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit->setUnitMapping($unitExternalMapping);
        $em->flush();

        $balanceSynchronizer = $this->getContainer()->get('amsi.contract_sync');
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
        $this->assertEquals('1605.00', $updatedContract->getIntegratedBalance(), 'Balance should update');
    }
}
