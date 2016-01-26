<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\ExternalApiBundle\Tests\Services\ContractSynchronizerTestBase as Base;

class ResManContractSynchronizerCase extends Base
{
    /**
     * @test
     */
    public function shouldSyncBalanceForContract()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);

        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->setIntegratedBalance(0);
        $contract->getHolding()->setAccountingSystem(AccountingSystem::RESMAN);
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $contract->setExternalLeaseId(ResManClientCase::EXTERNAL_LEASE_ID);
        $settings = $contract->getHolding()->getResManSettings();
        $settings->setSyncBalance(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $residentMapping = $contract->getTenant()->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);

        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId(ResManClientCase::EXTERNAL_UNIT_ID);
        $unitExternalMapping->setUnit($contract->getUnit());
        $contract->getUnit()->setUnitMapping($unitExternalMapping);

        $this->getEntityManager()->persist($unitExternalMapping);
        $this->getEntityManager()->flush();

        $this->getResManContractSynchronizer()->syncBalance();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertBalanceSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncBalanceCommand($contract->getHolding(), $externalPropertyId);

        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);

        $this->assertNotEquals(0, $contract->getIntegratedBalance(), 'Balance should be updated');
    }

    /**
     * @test
     */
    public function shouldSyncBalanceForContractWaitingBalance()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->getHolding()->setAccountingSystem(AccountingSystem::RESMAN);

        $settings = $contract->getHolding()->getResManSettings();
        $settings->setSyncBalance(true);

        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);

        $residentMapping = $contract->getTenant()->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);
        $contract->setStatus(ContractStatus::FINISHED);

        $contractWaiting = new ContractWaiting();
        $today = new \DateTime();
        $contractWaiting->setGroup($contract->getGroup());
        $contractWaiting->setProperty($contract->getProperty());
        $contractWaiting->setUnit($contract->getUnit());
        $contractWaiting->setRent($contract->getRent());
        $contractWaiting->setResidentId(ResManClientCase::RESIDENT_ID);
        $contractWaiting->setExternalLeaseId(ResManClientCase::EXTERNAL_LEASE_ID);
        $contractWaiting->setStartAt($today);
        $contractWaiting->setFinishAt($today);
        $contractWaiting->setFirstName('Papa');
        $contractWaiting->setLastName('Karlo');
        $contractWaiting->setIntegratedBalance(0);

        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId(ResManClientCase::EXTERNAL_UNIT_ID);
        $unitExternalMapping->setUnit($contract->getUnit());
        $contract->getUnit()->setUnitMapping($unitExternalMapping);

        $this->getEntityManager()->persist($unitExternalMapping);
        $this->getEntityManager()->persist($contractWaiting);
        $this->getEntityManager()->flush();

        $contractWaitingId = $contractWaiting->getId();

        $this->getResManContractSynchronizer()->syncBalance();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertBalanceSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncBalanceCommand($contract->getHolding(), $externalPropertyId);

        $contractWaiting = $this->getEntityManager()->find('RjDataBundle:ContractWaiting', $contractWaitingId);

        $this->assertNotNull($contractWaiting, 'ContractWaiting should exist');

        $this->assertNotEquals(0, $contractWaiting->getIntegratedBalance(), 'Balance should be updated');
    }

    /**
     * @test
     */
    public function shouldSyncContractRent()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);

        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->setRent(123321);
        $contract->getHolding()->setAccountingSystem(AccountingSystem::RESMAN);
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->getUnit()->setName(ResManClientCase::RESMAN_UNIT_ID);
        $contract->setExternalLeaseId(ResManClientCase::EXTERNAL_LEASE_ID);
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);

        $residentMapping = $contract->getTenant()->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);

        $this->getEntityManager()->flush();

        $this->getResManContractSynchronizer()->syncRent();

        $externalPropertyId = $contract
            ->getProperty()
            ->getPropertyMappingByHolding($contract->getHolding())
            ->getExternalPropertyId();
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);
        $this->assertRentSyncJob($lastJob, $contract->getHolding(), $externalPropertyId);

        $this->runSyncRentCommand($contract->getHolding(), $externalPropertyId);

        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);
        $this->assertNotEquals(123321, $contract->getRent(), 'Rent should be updated');
    }

    /**
     * @return \RentJeeves\ExternalApiBundle\Services\ResMan\ContractSynchronizer
     */
    protected function getResManContractSynchronizer()
    {
        return $this->getContainer()->get('resman.contract_sync');
    }
}
