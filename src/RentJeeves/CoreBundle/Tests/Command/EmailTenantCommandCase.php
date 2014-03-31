<?php
namespace RentJeeves\CoreBundle\Tests\Connamd;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CoreBundle\Command\EmailTenantCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class EmailTenantCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function testExecuteAutoPayment()
    {
        $this->load(true);
        static::$kernel = null;
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailTenantCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();
        
        $command = $application->find('Email:tenant');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--auto' => true,
                '--days' => 5
            )
        );
        $this->assertRegExp('/Start processing auto payment contracts/', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function testExecuteNonAutoPayment()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailTenantCommand());
    
        $plugin = $this->registerEmailListener();
        $plugin->clean();
    
        $command = $application->find('Email:tenant');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $this->assertRegExp('/Start processing non auto contracts/', $commandTester->getDisplay());
    }

    /**
     * Late payment
     * @test
     */
    public function textExecuteLate()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
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
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(2, $count);
    }
}
