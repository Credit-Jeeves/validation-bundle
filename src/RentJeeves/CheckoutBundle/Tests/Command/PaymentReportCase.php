<?php

namespace RentJeeves\ComponentBundle\Tests\Unit;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PaymentReportCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PaymentReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function testExecute()
    {
        $this->load(true);
        static::$kernel = null;
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new PaymentReportCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $command = $application->find('Payment:synchronize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(2, $count);
        $this->assertContains('Amount of synchronized payments: 6', $commandTester->getDisplay());
    }
}
