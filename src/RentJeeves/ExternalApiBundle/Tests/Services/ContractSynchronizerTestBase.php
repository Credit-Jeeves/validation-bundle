<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\TestBundle\Traits\JobAssertionTrait;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\ExternalApiBundle\Command\SyncContractRentCommand;
use RentJeeves\ExternalApiBundle\Command\SyncContractBalanceCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ContractSynchronizerTestBase extends BaseTestCase
{
    use JobAssertionTrait;
    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @return string
     */
    protected function runSyncRentCommand(Holding $holding, $externalPropertyId)
    {
        $application = new Application($this->getKernel());
        $application->add(new SyncContractRentCommand());
        $commandTester = new CommandTester($application->find(SyncContractRentCommand::NAME));
        $commandTester->execute([
            '--holding-id' => $holding->getId(),
            '--external-property-id' => $externalPropertyId,
        ]);

        return $commandTester->getDisplay();
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @return string
     */
    protected function runSyncBalanceCommand(Holding $holding, $externalPropertyId)
    {
        $application = new Application($this->getKernel());
        $application->add(new SyncContractBalanceCommand());
        $commandTester = new CommandTester($application->find(SyncContractBalanceCommand::NAME));
        $commandTester->execute([
            '--holding-id' => $holding->getId(),
            '--external-property-id' => $externalPropertyId,
        ]);

        return $commandTester->getDisplay();
    }

    /**
     * @param Job $job
     * @param Holding $holding
     * @param string $externalPropertyId
     */
    protected function assertRentSyncJob(Job $job, Holding $holding, $externalPropertyId)
    {
        $args = [
            '--holding-id=' . $holding->getId(),
            '--external-property-id=' . $externalPropertyId,
            '--app=rj',
        ];
        $this->assertJob($job, SyncContractRentCommand::NAME, $args);
    }

    /**
     * @param Job $job
     * @param Holding $holding
     * @param string $externalPropertyId
     */
    protected function assertBalanceSyncJob(Job $job, Holding $holding, $externalPropertyId)
    {
        $args = [
            '--holding-id=' . $holding->getId(),
            '--external-property-id=' . $externalPropertyId,
            '--app=rj',
        ];
        $this->assertJob($job, SyncContractBalanceCommand::NAME, $args);
    }
}
