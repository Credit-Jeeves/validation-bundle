<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

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
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::AMSI);
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->getHolding()->setRecurringCodes('RENT');
        $contract->setRent(123321); // test value

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

        $balanceSynchronizer = $this->getContainer()->get('amsi.contract_sync');
        $balanceSynchronizer->syncRecurringCharge();
        $updatedContract = $repo->find($contract->getId());

        $this->assertGreaterThan(0, $updatedContract->getRent(), 'Rent should be greater than 0');
        $this->assertNotEquals(123321, $updatedContract->getRent(), 'Rent should be updated');
    }

    /**
     * @test
     */
    public function shouldSyncContractBalanceForAMSI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::AMSI);
        $settings = $contract->getHolding()->getAmsiSettings();
        $settings->setSyncBalance(true);
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

        $balanceSynchronizer = $this->getContainer()->get('amsi.contract_sync');
        $balanceSynchronizer->syncBalance();
        $updatedContract = $repo->find($contract->getId());
        $this->assertLessThan(-4500, $updatedContract->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function shouldSyncContractWaitingBalanceForAMSI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repositoryContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting');
        $contractWaiting = $repositoryContractWaiting->findOneBy(['residentId' => 't0013535']);
        $this->assertNotNull($contractWaiting);
        $this->assertEquals(0, $contractWaiting->getIntegratedBalance());
        $contractWaiting->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::AMSI);
        $propertyMapping = $contractWaiting->getProperty()->getPropertyMappingByHolding(
            $contractWaiting->getGroup()->getHolding()
        );
        $propertyMapping->setExternalPropertyId(AMSIClientCase::EXTERNAL_PROPERTY_ID);
        $unit = $contractWaiting->getUnit();
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('001|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit->setUnitMapping($unitExternalMapping);
        $contractWaiting->setResidentId('296455');
        $settings = $contractWaiting->getGroup()->getHolding()->getAmsiSettings();
        $settings->setSyncBalance(true);
        $em->flush();

        $balanceSynchronizer = $this->getContainer()->get('amsi.contract_sync');
        $balanceSynchronizer->syncBalance();
        $updatedContract = $repositoryContractWaiting->find($contractWaiting->getId());
        $this->assertLessThan(-4500, $updatedContract->getIntegratedBalance());
    }
}
