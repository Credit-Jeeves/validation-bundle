<?php

namespace RentJeeves\ImportBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\ImportBundle\Command\ImportPropertyCommand;
use RentJeeves\ImportBundle\Command\ImportPropertyStatusCheckCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ImportPropertyStatusCheckCommandCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Entity Import#0 not found
     */
    public function shouldThrowExceptionIfImportNotFound()
    {
        $this->load(true);
        $this->executeCommandTester(new ImportPropertyCommand(), ['--import-id' => 0]);
    }

    /**
     * @test
     */
    public function shouldFinishedImportIfAllJobsForThisImportAreFinished()
    {
        $this->load(true);
        $import = $this->getEntityManager()->find('RjDataBundle:Import', 2);
        $this->assertNotEquals(ImportStatus::COMPLETE, $import->getStatus(), 'Import#2 should has status != COMPLETE');
        $this->assertNull($import->getFinishedAt(), 'Import#2 should has NULL for FinishedAt');

        $importId = 2;

        $job1 = new Job(
            'renttrack:import:property',
            [
                '--import-id=' . $importId,
            ]
        );
        $this->writeAttribute($job1, 'state', Job::STATE_FINISHED);
        $job2 = new Job(
            'renttrack:import:property',
            [
                '--import-id=' . $importId,
            ]
        );
        $this->writeAttribute($job2, 'state', Job::STATE_FINISHED);
        $job3 = new Job(
            'renttrack:import:property',
            [
                '--import-id=' . $importId,
            ]
        );
        $this->writeAttribute($job3, 'state', Job::STATE_FINISHED);

        $this->getEntityManager()->persist($job1);
        $this->getEntityManager()->persist($job2);
        $this->getEntityManager()->persist($job3);
        $this->getEntityManager()->flush();

        $application = new Application($this->getKernel());
        $syncCommand = new ImportPropertyStatusCheckCommand();

        $syncCommand->setContainer($this->getContainer());
        $application->add($syncCommand);

        $command = $application->find('renttrack:import:property:check-status');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--import-id' => $importId,
            ]
        );

        $this->getEntityManager()->refresh($import);
        $this->assertEquals(ImportStatus::COMPLETE, $import->getStatus(), 'Status is not updated');
        $this->assertNotNull($import->getFinishedAt(), 'FinishedAt is not updated');
    }
}
