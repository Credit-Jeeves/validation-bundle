<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\MigrateResidentsToLeaseIdCommand;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use Symfony\Component\Console\Tester\CommandTester;
use RentJeeves\TestBundle\Command\BaseTestCase;

class MigrateResidentsToLeaseIdCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateJobAndExecuteItSuccessfully()
    {
        $this->load(true);
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0011984']
        );

        $this->assertEmpty($contract, 'Contract should be empty');
        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->findOneBy(
            ['accountingSystem' => AccountingSystem::YARDI_VOYAGER]
        );
        $this->assertNotEmpty($holding, 'Holding does not exist in fixtures');
        $holding->setAccountingSystem(AccountingSystem::PROMAS);
        $this->getEntityManager()->flush();
        $job = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findOneBy(
            ['command' => 'renttrack:migrate:residents-to-lease-id']
        );
        $this->assertEmpty($job, 'We have job in DB');
        $this->executeCommandTester(
            new MigrateResidentsToLeaseIdCommand(),
            [
                '--accounting-system' => AccountingSystem::PROMAS,
            ]
        );

        $job = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findOneBy(
            ['command' => 'renttrack:migrate:residents-to-lease-id']
        );
        $this->assertNotEmpty($job, 'Job not created.');
        $args = $job->getArgs();
        $this->assertArrayHasKey(0, $args, 'We don\'t have key with contracts id');
        $leasesId = $args[0];
        list(, $values) = explode('=', $leasesId);

        $this->executeCommandTester(
            new MigrateResidentsToLeaseIdCommand(),
            [
                '--accounting-system' => AccountingSystem::PROMAS,
                '--leases-id' => $values
            ]
        );

        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0011984']
        );

        $this->assertNotEmpty($contract, 'We didn\'t update contract');
    }
}
