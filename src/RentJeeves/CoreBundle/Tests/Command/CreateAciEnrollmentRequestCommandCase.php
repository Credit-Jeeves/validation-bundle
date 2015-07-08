<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\CreateAciEnrollmentRequestCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class CreateAciEnrollmentRequestCommandCase extends BaseTestCase
{
    /**
     * remove created file
     */
    protected function tearDown()
    {
        parent::tearDown();
        if (true === file_exists($this->getFilePath())) {
            unlink($this->getFilePath());
        }
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return __DIR__ . '/test.csv';
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfSendNotCorrectHoldingId()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
                '--holding_id' => 123456,
            ]
        );
    }

    /**
     * @test
     */
    public function shouldCreateFileWithRowsForHoldingIfSendHolding()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
                '--holding_id' => 5,
            ]
        );

        $fileData = file_get_contents($this->getFilePath());
        $this->assertEquals(5, count(explode("\n", $fileData)));
    }

    /**
     * @test
     */
    public function shouldCreateFileWithRowsForAllHoldingIfNotSendHolding()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
            ]
        );

        $fileData = file_get_contents($this->getFilePath());
        $this->assertEquals(7, count(explode("\n", $fileData)));
    }
}
