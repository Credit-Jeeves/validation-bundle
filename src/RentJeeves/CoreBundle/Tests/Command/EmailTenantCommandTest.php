<?php
namespace RentJeeves\CoreBundle\Tests\Connamd;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CoreBundle\Command\EmailTenantCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class EmailTenantCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    public function testExecute()
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
                '--auto' => true,
                '--days' => 6
            )
        );
        //$this->assertEquals('Finished command "Email:tenant --auto"', $commandTester->getDisplay());
        $this->assertRegExp('/Finished command/', $commandTester->getDisplay());
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(1, $count);
    }
}
