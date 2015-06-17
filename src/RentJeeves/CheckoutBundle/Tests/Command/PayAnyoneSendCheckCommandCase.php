<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\CheckoutBundle\Command\PayAnyoneSendCheckCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PayAnyoneSendCheckCommandCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Order with id#12345678 not found
     */
    public function shouldThrowExceptionIfOrderNotFound()
    {
        $application = new Application($this->getKernel());
        $application->add(new PayAnyoneSendCheckCommand());

        $command = $application->find('payment:pay-anyone:send-check');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--jms-job-id' => 1,
                'order-id' => 12345678
            ]
        );
    }
}
