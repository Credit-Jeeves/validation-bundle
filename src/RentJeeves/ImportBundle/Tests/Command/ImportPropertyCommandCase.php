<?php

namespace RentJeeves\ImportBundle\Tests\Command;

use RentJeeves\ImportBundle\Command\ImportPropertyCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ImportPropertyCommandCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Both options("path-to-file" and "external-property-id") are specified
     */
    public function shouldThrowExceptionIfBothOptionsAreSpecified()
    {
        $this->load(true);
        $this->executeCommandTester(
            new ImportPropertyCommand(),
            [
                '--import-id' => 1,
                '--path-to-file' => 'test',
                '--external-property-id' => 'test',
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Neither option is specified
     */
    public function shouldThrowExceptionIfNeitherOptionIsSpecified()
    {
        $this->load(true);
        $this->executeCommandTester(
            new ImportPropertyCommand(),
            [
                '--import-id' => 1,
            ]
        );
    }

    /**
     * @test
     */
    public function shouldCallImportIfInputDataIsValidForApi()
    {
        $application = new Application($this->getKernel());
        $syncCommand = new ImportPropertyCommand();

        $importPropertyManager = $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\ImportPropertyManager');
        $importPropertyManager->expects($this->once())
            ->method('import');
        $this->getContainer()->set('import.property.manager', $importPropertyManager);

        $syncCommand->setContainer($this->getContainer());
        $application->add($syncCommand);

        $command = $application->find('renttrack:import:property');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--import-id' => 1,
                '--external-property-id' => 'rnttrk01'
            ]
        );
    }

    /**
     * @test
     */
    public function shouldCallImportIfInputDataIsValidForCsv()
    {
        $application = new Application($this->getKernel());
        $syncCommand = new ImportPropertyCommand();

        $importPropertyManager = $this->getBaseMock('\RentJeeves\ImportBundle\PropertyImport\ImportPropertyManager');
        $importPropertyManager->expects($this->once())
            ->method('import');
        $this->getContainer()->set('import.property.manager', $importPropertyManager);

        $syncCommand->setContainer($this->getContainer());
        $application->add($syncCommand);

        $command = $application->find('renttrack:import:property');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--import-id' => 1,
                '--path-to-file' => 'test'
            ]
        );
    }
}
