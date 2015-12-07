<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\ExternalApiBundle\Command\YardiPushReversalReceiptCommand;
use RentJeeves\ExternalApiBundle\Command\YardiReversalReceiptCollectCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class YardiReversalReceiptCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckCollectCommand()
    {
        $this->getKernel()->getContainer()->set(
            'yardi.reversal_receipts',
            $this->getReversalReceiptSenderMock('сollectingReversalPaymentsToJobsForDate')
        );
        $application = new Application($this->getKernel());
        $commandYardi = new YardiReversalReceiptCollectCommand();
        $application->add($commandYardi);

        $command = $application->find('api:yardi:collect-reversal-receipts');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }

    /**
     * @test
     */
    public function shouldCheckPushCommand()
    {
        $this->getKernel()->getContainer()->set(
            'yardi.reversal_receipts',
            $this->getReversalReceiptSenderMock('pushReversedReceiptByOrderId')
        );
        $application = new Application($this->getKernel());
        $commandYardi = new YardiPushReversalReceiptCommand();
        $application->add($commandYardi);

        $command = $application->find('api:yardi:push-reversal-receipt');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }

    /**
     * @param string $method
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getReversalReceiptSenderMock($method)
    {
        $reversalReceiptSender = $this->getMock(
            'RentJeeves\ExternalApiBundle\Services\Yardi\ReversalReceiptSender',
            [$method],
            [],
            '',
            false
        );
        $reversalReceiptSender
            ->expects($this->exactly(1))
            ->method($method)
            ->will($this->returnValue(true));

        return $reversalReceiptSender;
    }
}
