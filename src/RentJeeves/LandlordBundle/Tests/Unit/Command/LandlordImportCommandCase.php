<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Command;

use RentJeeves\LandlordBundle\Command\LandlordImportCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Config\FileLocator;

class LandlordImportCommandCase extends BaseTestCase
{
    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Partner with name 'badName' not found
     */
    public function shouldThrowExceptionIfSendNotCorrectPartnerName()
    {
        $this->load(true);

        $application = new Application($this->getKernel());
        $application->add(new LandlordImportCommand());

        $command = $application->find('renttrack:landlord:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--partner-name' => 'badName',
            ]
        );
    }

    /**
     * @test
     */
    public function shouldDisplayErrorIfSendNotCorrectFilePath()
    {
        $this->load(true);

        $application = new Application($this->getKernel());
        $application->add(new LandlordImportCommand());

        $command = $application->find('renttrack:landlord:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--partner-name' => 'creditcom',
                '--path' => '/badFilePath.csv',
            ]
        );

        $this->assertContains('File "/badFilePath.csv" not found', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function shouldDisplayErrorForIncorrectRow()
    {
        $this->markTestSkipped('Skip this test because we need refactoring LandlordCsvImport');
        $this->load(true);

        $path = $pathToFile = $this->getFileLocator()
            ->locate('@LandlordBundle/Tests/Fixtures/importFile.csv');

        $application = new Application($this->getKernel());
        $application->add(new LandlordImportCommand());

        $command = $application->find('renttrack:landlord:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--partner-name' => 'creditcom',
                '--path' => $path,
            ]
        );

        $this->assertContains('email : This value is not a valid email address', $commandTester->getDisplay());
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
