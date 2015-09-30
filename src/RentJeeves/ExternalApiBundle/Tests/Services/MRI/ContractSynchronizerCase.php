<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\MRI;

use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ContractSynchronizerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSyncContractBalanceForMRI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'Should have contract in fixtures');
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->setPaymentAccepted(null);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::MRI);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
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
        $balanceSynchronizer->syncBalance();
        $updatedContract = $repo->find($contract->getId());
        $this->assertGreaterThan(8340, (int) $updatedContract->getIntegratedBalance(), 'Balance not updated');
        $this->assertEquals(0, $updatedContract->getPaymentAccepted(), 'PaymentAccepted should be set');
    }

    /**
     * @test
     */
    public function shouldSyncContracWaitingBalanceForMRI()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $repositoryContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting');
        $contractWaiting = $repositoryContractWaiting->findOneBy(['residentId' => 't0013535']);
        $this->assertNotNull($contractWaiting, 'We should find contract waiting with resident t0013535');
        $this->assertEquals(0, $contractWaiting->getIntegratedBalance(), 'Balance should be 0');
        $contractWaiting->setPaymentAccepted(null);
        $contractWaiting->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::MRI);
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
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'Should have contract in fixtures');
        $contract->setRent(0);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::MRI);
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
        $balanceSynchronizer->syncRecurringCharge();
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
        $contractWaiting = $repositoryContractWaiting->findOneBy(['residentId' => 't0013535']);
        $this->assertNotNull($contractWaiting, 'We should find contract waiting with resident t0013535');
        $contractWaiting->setRent(0);
        $contractWaiting->getGroup()->getHolding()->setApiIntegrationType(ApiIntegrationType::MRI);
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
        $balanceSynchronizer->syncRecurringCharge();
        /** @var ContractWaiting $updatedContractWaiting */
        $updatedContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($contractWaiting->getId());
        $this->assertGreaterThan(0, (int) $updatedContractWaiting->getRent(), 'Balance not updated');
    }

    /**
     * @return array
     */
    public function dateProvider()
    {
        return [
            [$startDate = new \DateTime('-1 day'), $endDate = new \DateTime(), true],
            [$startDate = new \DateTime('+1 day'), $endDate = new \DateTime(), false],
            [$startDate = new \DateTime('-1 day'), null, true],
            [null, $endDate = new \DateTime('-1 day'), false],
            [null, $endDate = new \DateTime('+1 day'), true],
            [$startDate = new \DateTime('+1 day'), $endDate = new \DateTime('-1 day'), false],
            [$startDate = new \DateTime('-1 year'), null, true]
        ];
    }

    /**
     * @test
     * @dataProvider dateProvider
     */
    public function shouldCheckDateFallsBetweenDates($startDate, $endDate, $result)
    {
        $contractSync = $this->getContainer()->get('mri.contract_sync');
        $contractSyncReflectionClass = new \ReflectionClass($contractSync);

        $doesDateFallBetweenDateMethod = $contractSyncReflectionClass->getMethod('checkDateFallsBetweenDates');
        $doesDateFallBetweenDateMethod->setAccessible(true);

        $resultExecute = $doesDateFallBetweenDateMethod->invokeArgs(
            $contractSync,
            [
                $startDate,
                $endDate
            ]
        );

        $this->assertEquals($result, $resultExecute);
    }
}
