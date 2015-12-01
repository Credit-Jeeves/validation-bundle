<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\CheckoutBundle\Command\PaymentReportSynchronizeAciPayAnyoneCommand;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class PaymentReportSynchronizeAciPayAnyoneCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCallReportSyncAndPassCorrectData()
    {
        $application = new Application($this->getKernel());
        $syncCommand = new PaymentReportSynchronizeAciPayAnyoneCommand();
        $syncCommand->setContainer($this->getContainerMock());
        $application->add($syncCommand);

        $command = $application->find('payment:report:synchronize:aci:payanyone');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName()
            ]
        );

        $this->assertEquals('Amount of synchronized payments: 0', trim($commandTester->getDisplay()));
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getContainerMock()
    {
        $paymentManager = $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\PaymentManager',
            [],
            [],
            '',
            false
        );

        $reportLoader = $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\ReportLoader',
            ['loadReport'],
            [],
            '',
            false
        );

        $reportLoader
            ->expects($this->once())
            ->method('loadReport')
            ->will($this->returnValue(new PaymentProcessorReport()));

        $paymentProcessor = new PaymentProcessorAciPayAnyone($paymentManager, $reportLoader);

        $this->getContainer()->set('payment_processor.aci_pay_anyone', $paymentProcessor);

        return $this->getContainer();
    }
}
