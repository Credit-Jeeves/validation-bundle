<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Operation;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\AccountingSystem;
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
     * @test
     */
    public function shouldRetryFailedJobsWhenContractsGetsExternalLeaseId()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);

        $contract->getHolding()->setAccountingSystem(AccountingSystem::RESMAN);
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->getUnit()->setName('test_resman_external_property_id|1|1101');

        $contract->setExternalLeaseId(null);
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId('test_resman_external_property_id');

        $residentMapping = $contract->getTenant()->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId('0529ec65-1310-47aa-8570-a902b927f92c');

        $unitMapping = new UnitMapping();
        $unitMapping->setExternalUnitId('test_resman_external_property_id|1|1101');
        $unitMapping->setUnit($contract->getUnit());
        $contract->getUnit()->setUnitMapping($unitMapping);
        $this->getEntityManager()->persist($unitMapping);

        /** @var Operation $operation */
        $this->assertNotEmpty($operation = $contract->getOperations()->first(), 'Contract should has operations');
        $order = $operation->getOrder();
        $jobRelatedToOrder = new JobRelatedOrder();
        $jobRelatedToOrder->setOrder($order);
        $jobRelatedToOrder->setCreatedAt(new \DateTime());
        $failedJob = new Job('external_api:payment:push');
        $failedJob->addRelatedEntity($jobRelatedToOrder);
        $failedJob->setState(Job::STATE_FAILED, $force = true);
        $this->getEntityManager()->persist($failedJob);
        $this->getEntityManager()->persist($jobRelatedToOrder);

        $this->getEntityManager()->flush();

        $this->getResManContractSynchronizer()->syncBalanceForHoldingAndExternalPropertyId(
            $contract->getHolding(),
            'test_resman_external_property_id'
        );

        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        $lastJob = end($jobs);

        $this->assertEquals($failedJob->getId(), $lastJob->getOriginalJob()->getId(), 'JobIds should be equals');
        $this->assertEquals('pending', $lastJob->getState(), 'Job should be pending');
        $this->assertEquals(
            'external_api:payment:push',
            $lastJob->getCommand(),
            'Expected: New job is for external_api:payment:push'
        );
        $this->assertEquals(
            $order->getId(),
            $lastJob->getRelatedEntities()->first()->getOrder()->getId(),
            'Order\'s IDs should be equal'
        );
    }

    /**
     * @return \RentJeeves\ExternalApiBundle\Services\ResMan\ContractSynchronizer
     */
    protected function getResManContractSynchronizer()
    {
        return $this->getContainer()->get('resman.contract_sync');
    }
}
