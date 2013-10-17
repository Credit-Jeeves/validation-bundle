<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PaymentCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PaymentCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function testExecute()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new PaymentCommand());
        
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        
        $command = $application->find('Payment:process');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(3, $count);
        $this->assertRegExp('/Start payment process(.*)OK/', $commandTester->getDisplay());
    }
}
