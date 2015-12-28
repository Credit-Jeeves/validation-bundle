<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Command\ResManSyncBalanceCommand;
use RentJeeves\ExternalApiBundle\Command\SyncContractBalanceCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\JobAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ResManSyncBalanceCommandCase extends BaseTestCase
{
    use JobAssertionTrait;
    /**
     * @test
     */
    public function shouldCreateJobForSyncBalance()
    {
        $this->load(true);

        $this->getEntityManager()->getConnection()->update('jms_jobs', ['state' => 'finished'], ['state' => 'pending']);
        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->find(5);
        $this->assertNotNull($holding, 'Check fixtures, should present holding with id = 5');
        $holding->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $holding->getResManSettings()->setSyncBalance(true);
        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(1);
        $this->assertNotNull($property, 'Check fixtures, should exist property with id = 1');
        $propertyMapping = $property->getPropertyMappingByHolding($holding);
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);

        $this->getEntityManager()->flush();

        $application = new Application($this->getKernel());
        $application->add(new ResManSyncBalanceCommand());

        $command = $application->find('api:resman:sync-balance');
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
            SyncContractBalanceCommand::NAME,
            [
                '--holding-id=' . $holding->getId(),
                '--external-property-id=' . ResManClientCase::EXTERNAL_PROPERTY_ID,
                '--app=rj',
            ]
        );
    }
}
