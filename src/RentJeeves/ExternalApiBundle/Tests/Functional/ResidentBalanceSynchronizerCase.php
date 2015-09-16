<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\ExternalApiBundle\Tests\Services\MRI\MRIClientCase;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ResidentBalanceSynchronizerCase extends BaseTestCase
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
        $this->assertNotNull($contract, 'Must find contract');
        $this->assertEquals(0, $contract->getIntegratedBalance());
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

        $balanceSynchronizer = $this->getContainer()->get('mri.resident_balance_sync');
        $balanceSynchronizer->run();
        $updatedContract = $repo->find($contract->getId());
        $this->assertGreaterThan(8340, (int) $updatedContract->getIntegratedBalance(), 'Balance not updated');
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
        $this->assertEquals(0, $contractWaiting->getIntegratedBalance(), 'Balance should not be 0');
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

        $balanceSynchronizer = $this->getContainer()->get('mri.resident_balance_sync');
        $balanceSynchronizer->run();
        /** @var ContractWaiting $updatedContractWaiting */
        $updatedContractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($contractWaiting->getId());
        $this->assertGreaterThan(8340, (int) $updatedContractWaiting->getIntegratedBalance(), 'Balance not updated');
    }
}
