<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Command\ResManSyncRentCommand;
use RentJeeves\ExternalApiBundle\Command\SyncContractRentCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ResManSyncRentCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSyncContractRent()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(20);
        $contract->setRent(123321);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->setExternalLeaseId(ResManClientCase::EXTERNAL_LEASE_ID);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $contract->getUnit()->setName(ResManClientCase::RESMAN_UNIT_ID);
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId(ResManClientCase::RESIDENT_ID);
        $this->getEntityManager()->flush();

        $application = new Application($this->getKernel());
        $application->add(new ResManSyncRentCommand());

        $command = $application->find('api:resman:sync-rent');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();

        $this->assertNotEmpty($jobs, 'Should be find at least one job');
        /** @var Job $lastJob */
        $lastJob = end($jobs);

        $this->assertContains(
            sprintf(
                '[ResMan ContractSynchronizer][SyncRent]Created job#%d: to sync rent for holding: %s(#%d),' .
                ' external property: %s',
                $lastJob->getId(),
                $contract->getHolding()->getName(),
                $contract->getHolding()->getId(),
                ResManClientCase::EXTERNAL_PROPERTY_ID
            ),
            $commandTester->getDisplay(),
            'Job was not be created'
        );

        $this->assertEquals(
            SyncContractRentCommand::NAME,
            $lastJob->getCommand(),
            'Command name on job is incorrect'
        );

        $this->assertEquals(
            [
                '--holding-id=' . $contract->getHolding()->getId(),
                '--external-property-id=' . ResManClientCase::EXTERNAL_PROPERTY_ID,
                '--app=rj',
            ],
            $lastJob->getArgs(),
            'Arguments on job are incorrect'
        );
    }
}
