<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ResidentBalanceSynchronizerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSyncContractBalance()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->findOneBy(array('rent' => 850, 'balance' => -250));
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());

        $balanceSyncronizer = $this->getContainer()->get('yardi.resident_balance_sync');
        $balanceSyncronizer->run();
        $updatedContract = $repo->find($contract->getId());
        $this->assertGreaterThan(4360.5, $updatedContract->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function shouldSyncContractWaitingBalance()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');

        $contract = $repo->findOneBy(array('rent' => 850, 'balance' => -250));
        $this->assertNotNull($contract);
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);

        $contractWaiting = new ContractWaiting();
        $today = new DateTime();
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
        $em->flush($contractWaiting);

        $this->assertEquals(0, $contractWaiting->getIntegratedBalance());

        $balanceSynchronizer = $this->getContainer()->get('yardi.resident_balance_sync');
        $balanceSynchronizer->run();

        $repo = $em->getRepository('RjDataBundle:ContractWaiting');
        $updatedContractWaiting = $repo->findByHoldingPropertyUnitResident(
            $contract->getGroup()->getHolding(),
            $contract->getProperty(),
            $contract->getUnit()->getName(),
            't0011984'
        );
        $this->assertNotNull($updatedContractWaiting);
        $this->assertGreaterThan(4360.5, $updatedContractWaiting->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function shouldSyncContractBalanceResMan()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->findOneBy(['rent' => 850, 'balance' => -250]);
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->setIntegratedBalance(-2000.00);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $settings = $contract->getHolding()->getResManSettings();
        $settings->setSyncBalance(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $contract->getUnit()->setName('2');
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);
        $em->flush();
        $balanceSynchronizer = $this->getContainer()->get('resman.resident_balance_sync');
        $balanceSynchronizer->run();
        $updatedContract = $repo->find($contract->getId());
        $this->assertEquals(0, $updatedContract->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function shouldSyncContractWaitingBalanceResMan()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');

        $contract = $repo->findOneBy(['rent' => 850, 'balance' => -250]);
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->setIntegratedBalance(-2000.00);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $settings = $contract->getHolding()->getResManSettings();
        $settings->setSyncBalance(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $contract->getUnit()->setName('2');
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);

        $contractWaiting = new ContractWaiting();
        $today = new DateTime();
        $contractWaiting->setGroup($contract->getGroup());
        $contractWaiting->setProperty($contract->getProperty());
        $contractWaiting->setUnit($contract->getUnit()->setName('2'));
        $contractWaiting->setRent($contract->getRent());
        $contractWaiting->setResidentId(ResManClientCase::RESIDENT_ID);
        $contractWaiting->setStartAt($today);
        $contractWaiting->setFinishAt($today);
        $contractWaiting->setFirstName('Papa');
        $contractWaiting->setLastName('Karlo');
        $em->persist($contractWaiting);
        $em->flush($contractWaiting);

        $this->assertEquals(0, $contractWaiting->getIntegratedBalance());

        $balanceSynchronizer = $this->getContainer()->get('yardi.resident_balance_sync');
        $balanceSynchronizer->run();

        $repo = $em->getRepository('RjDataBundle:ContractWaiting');
        $updatedContractWaiting = $repo->findByHoldingPropertyUnitResident(
            $contract->getGroup()->getHolding(),
            $contract->getProperty(),
            $contract->getUnit()->getName(),
            ResManClientCase::RESIDENT_ID
        );
        $this->assertNotNull($updatedContractWaiting);
        $this->assertEquals(0, $updatedContractWaiting->getIntegratedBalance());
    }
}
