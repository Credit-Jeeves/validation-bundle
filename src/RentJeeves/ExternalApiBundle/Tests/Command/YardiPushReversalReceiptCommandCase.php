<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\ExternalApiBundle\Command\YardiPushReversalReceiptCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class YardiPushReversalReceiptCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckPushCommand()
    {
        $reversalReceiptSender = $this->getMock(
            'RentJeeves\ExternalApiBundle\Services\Yardi\ReversalReceiptSender',
            ['pushReversedReceiptByOrderId'],
            [],
            '',
            false
        );
        $reversalReceiptSender
            ->expects($this->exactly(1))
            ->method('pushReversedReceiptByOrderId')
            ->will($this->returnValue(true));

        $this->getKernel()->getContainer()->set(
            'yardi.reversal_receipts',
            $reversalReceiptSender
        );
        $application = new Application($this->getKernel());
        $commandYardi = new YardiPushReversalReceiptCommand();
        $application->add($commandYardi);

        $command = $application->find('renttrack:yardi:push-reversal-receipt');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }
}
