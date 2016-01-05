<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\ExternalApiBundle\Command\YardiReversalReceiptCollectCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class YardiReversalReceiptCollectCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckCollectCommand()
    {
        $reversalReceiptSender = $this->getMock(
            'RentJeeves\ExternalApiBundle\Services\Yardi\ReversalReceiptSender',
            ['collectReversedPaymentsToJobsByDate'],
            [],
            '',
            false
        );
        $reversalReceiptSender
            ->expects($this->exactly(1))
            ->method('collectReversedPaymentsToJobsByDate')
            ->will($this->returnValue(true));

        $this->getKernel()->getContainer()->set(
            'yardi.reversal_receipts',
            $reversalReceiptSender
        );
        $application = new Application($this->getKernel());
        $commandYardi = new YardiReversalReceiptCollectCommand();
        $application->add($commandYardi);

        $command = $application->find('renttrack:yardi:collect-reversal-receipts');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }
}
