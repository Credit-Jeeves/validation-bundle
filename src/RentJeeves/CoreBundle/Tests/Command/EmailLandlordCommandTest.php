<?php
namespace RentJeeves\CoreBundle\Tests\Connamd;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CoreBundle\Command\EmailLandlordCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class EmailLandlordCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    public function testExecute()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();
        
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $this->assertRegExp('/Story-1555/', $commandTester->getDisplay());
    }

    /**
     * Story-2042
     * Contracts with status="pending"
     * @test
     */
    public function testExecutePending()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'pending'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(2, $count);
        $this->assertRegExp('/Story-2042/', $commandTester->getDisplay());
    }

    /**
     * Story-1555
     * @test
     */
    public function testExecutePaid()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'paid'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(1, $count);
        $this->assertRegExp('/Story-1555/', $commandTester->getDisplay());
    }

    /**
     * Story-1560
     * @test
     */
    public function testExecuteNotPaid()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'nsf'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(1, $count);
        $this->assertRegExp('/Story-1560/', $commandTester->getDisplay());
    }

    public function testExecureReport()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'report'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(3, $count);
        $this->assertRegExp('/daily report/', $commandTester->getDisplay());
    }
}
