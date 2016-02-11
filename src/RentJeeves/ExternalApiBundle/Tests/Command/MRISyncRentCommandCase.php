<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Command\MRISyncRentCommand;
use RentJeeves\ExternalApiBundle\Command\SyncContractRentCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\MRI\MRIClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\JobAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MRISyncRentCommandCase extends BaseTestCase
{
    use JobAssertionTrait;
    /**
     * @test
     */
    public function shouldCreateJobsForSyncRent()
    {
        $this->load(true);

        $this->getEntityManager()->getConnection()->update('jms_jobs', ['state' => 'finished'], ['state' => 'pending']);
        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->find(5);
        $this->assertNotNull($holding, 'Check fixtures, should present holding with id = 5');
        $holding->setAccountingSystem(AccountingSystem::MRI);
        $holding->setUseRecurringCharges(true);
        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(1);
        $this->assertNotNull($property, 'Check fixtures, should exist property with id = 1');
        $propertyMapping = $property->getPropertyMappingByHolding($holding);
        $propertyMapping->setExternalPropertyId(MRIClientCase::PROPERTY_ID);

        $this->getEntityManager()->flush();

        $application = new Application($this->getKernel());
        $application->add(new MRISyncRentCommand());

        $command = $application->find('api:mri:sync-rent');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findBy([
            'state' => 'pending'
        ]);

        $this->assertCount(1, $jobs, 'Should be found just one ready job');

        $this->assertJob(
            end($jobs),
            SyncContractRentCommand::NAME,
            [
                '--holding-id=' . $holding->getId(),
                '--external-property-id=' . MRIClientCase::PROPERTY_ID,
                '--app=rj',
            ]
        );
    }
}
