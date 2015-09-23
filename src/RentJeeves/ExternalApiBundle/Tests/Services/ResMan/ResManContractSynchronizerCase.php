<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ResManContractSynchronizerCase extends Base
{
    /**
     * @test
     */
    public function shouldSyncBalanceForContract()
    {
        $this->load(true);

        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);

        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->setIntegratedBalance(0);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $contract->setExternalLeaseId(ResManClientCase::EXTERNAL_LEASE_ID);
        $settings = $contract->getHolding()->getResManSettings();
        $settings->setSyncBalance(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $contract->getUnit()->setName(ResManClientCase::RESMAN_UNIT_ID);

        $residentMapping = $contract->getTenant()->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);

        $this->getEntityManager()->flush();

        $this->getResManContractSynchronizer()->syncBalance();

        $this->getEntityManager()->clear($contract);

        $this->assertNotEquals(0, $contract->getIntegratedBalance(), 'Balance should be updated');
    }

    /**
     * @test
     */
    public function shouldSyncBalanceForContractWaitingBalance()
    {
        $this->load(true);

        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $contract->getUnit()->setName(ResManClientCase::RESMAN_UNIT_ID);

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

        $this->getEntityManager()->persist($contractWaiting);
        $this->getEntityManager()->flush();

        $this->getResManContractSynchronizer()->syncBalance();

        $this->getEntityManager()->clear($contractWaiting);

        $this->assertNotNull($contractWaiting);

        $this->assertNotEquals(0, $contractWaiting->getIntegratedBalance(), 'Balance should be updated');
    }

    /**
     * @test
     */
    public function shouldSyncContractRent()
    {
        $this->load(true);

        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 20);

        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->setRent(123321);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->getUnit()->setName(ResManClientCase::RESMAN_UNIT_ID);
        $contract->setExternalLeaseId(ResManClientCase::EXTERNAL_LEASE_ID);
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);

        $residentMapping = $contract->getTenant()->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);

        $this->getEntityManager()->flush();

        $this->getResManContractSynchronizer()->syncRecurringCharge();

        $this->getEntityManager()->clear($contract);
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
