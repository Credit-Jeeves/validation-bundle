<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class YardiContractSynchronizerCase extends Base
{
    /**
     * @test
     */
    public function shouldSyncContractBalance()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(
            0,
            $contract->getIntegratedBalance(),
            'Contract in fixture should have 9 integrated balance.'
        );

        $balanceSynchronizer= $this->getContainer()->get('yardi.contract_sync');
        $balanceSynchronizer->syncBalance();
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
    public function shouldSyncContractWaitingBalance()
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');

        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'We should have contract in fixture.');
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);

        $contractWaiting = new ContractWaiting();
        $today = new \DateTime();
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

        $this->assertEquals(
            0,
            $contractWaiting->getIntegratedBalance(),
            'Integrated balance for Contract Waiting should be 0. Because this is new.'
        );

        $balanceSynchronizer = $this->getContainer()->get('yardi.contract_sync');
        $balanceSynchronizer->syncBalance();

        $repo = $em->getRepository('RjDataBundle:ContractWaiting');
        $updatedContractWaiting = $repo->findByHoldingPropertyUnitResident(
            $contract->getGroup()->getHolding(),
            $contract->getProperty(),
            $contract->getUnit()->getName(),
            't0011984'
        );
        $this->assertNotNull($updatedContractWaiting, 'We should find contract waiting which we just created');
        $this->assertGreaterThan(
            4360.5,
            $updatedContractWaiting->getIntegratedBalance(),
            'We did not update integrated balance for contract waiting'
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

        $holding->setUseRecurringCharges(true);
        $holding->setRecurringCodes('.rent,sss');
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $em->flush();

        $contractSynchronizer = $this->getContainer()->get('yardi.contract_sync');
        $contractSynchronizer->syncRent();
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'Did not find contract which should be updated');
        $this->assertEquals(900.00, $contract->getRent(), 'Rent didn\'t updated');
    }

    /**
     * @test
     */
    public function shouldSyncContractWaitingRentForYardi()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find(20);
        $this->assertNotNull($contract, 'We didn\'t find contract in fixtures.');
        $contract->setStatus(ContractStatus::FINISHED);
        $em->flush($contract);
        $unit = $contract->getUnit();
        $unit->setName('101');
        $contractWaiting = new ContractWaiting();
        $today = new \DateTime();
        $contractWaiting->setGroup($contract->getGroup());
        $contractWaiting->setProperty($contract->getProperty());
        $contractWaiting->setUnit($contract->getUnit());
        $contractWaiting->setRent($contract->getRent());
        $contractWaiting->setResidentId('t0012027');
        $contractWaiting->setExternalLeaseId('t0012027');
        $contractWaiting->setStartAt($today);
        $contractWaiting->setFinishAt($today);
        $contractWaiting->setFirstName('Papa');
        $contractWaiting->setLastName('Karlo');
        $em->persist($contractWaiting);
        $em->flush($contractWaiting);

        $this->assertEquals(
            850.00,
            $contractWaiting->getRent(),
            'Contract rent in fixtures is wrong.'
        );

        $holding = $contractWaiting->getGroup()->getHolding();
        $holding->setUseRecurringCharges(true);
        $holding->setRecurringCodes('.rent,sss');
        $contract->getGroup()->getGroupSettings()->setIsIntegrated(true);
        $em->flush();

        $contractSyncronizer = $this->getContainer()->get('yardi.contract_sync');
        $contractSyncronizer->syncRent();
        /** @var Contract $contract */
        $this->assertEquals(
            900.00,
            $contractWaiting->getRent(),
            'Rent contract waiting didn\'t update'
        );
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
        $contractSync = $this->getContainer()->get('yardi.contract_sync');
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
