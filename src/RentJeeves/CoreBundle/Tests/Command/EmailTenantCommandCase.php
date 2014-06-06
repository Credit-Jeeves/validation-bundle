<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CoreBundle\Command\EmailTenantCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class EmailTenantCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executeAutoPayment()
    {
        $this->load(true);
        static::$kernel = null;
        $application = new Application($this->getKernel());
        $application->add(new EmailTenantCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();
        
        $command = $application->find('Email:tenant');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--auto' => true,
            )
        );
        $this->assertRegExp('/Start processing auto payment contracts/', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeNonAutoPayment()
    {
        $this->load(false);
        $application = new Application($this->getKernel());
        $application->add(new EmailTenantCommand());
    
        $plugin = $this->registerEmailListener();
        $plugin->clean();
    
        $command = $application->find('Email:tenant');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--days' => 0,
            )
        );
        $this->assertRegExp('/Start processing non auto contracts/', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeDueAutoPayment()
    {
        $this->load(false);
        $application = new Application($this->getKernel());
        $application->add(new EmailTenantCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $command = $application->find('Email:tenant');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--days' => 0,
                '--auto' => true,
            )
        );
        $this->assertRegExp(
            '/Start processing auto payment contracts.*Finished command/',
            $commandTester->getDisplay()
        );
        $this->assertCount(1, $plugin->getPreSendMessages());
    }

    /**
     * Late payment
     * @test
     */
    public function executeLate()
    {
        $this->load(false);
        $application = new Application($this->getKernel());
        $application->add(new EmailTenantCommand());
        
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        
        $command = $application->find('Email:tenant');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'late',
            )
        );
        $this->assertRegExp('/Start processing late contracts/', $commandTester->getDisplay());
        $this->assertCount(2, $plugin->getPreSendMessages());// Contracts with ids: 7, 18
    }
}
