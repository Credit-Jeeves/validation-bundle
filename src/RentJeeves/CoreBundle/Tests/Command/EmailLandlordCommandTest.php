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
        //$this->assertEquals('Story-1555', $commandTester->getDisplay());
        $this->assertRegExp('/Story-1555/', $commandTester->getDisplay());
//         $this->assertNotNull($count = $plugin->getPreSendMessages());
//         $this->assertCount(1, $count);
    }
}
