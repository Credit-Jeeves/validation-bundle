<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\CheckoutBundle\Command\PaymentReportSynchronizeAciCollectV4Command;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PaymentReportSynchronizeAciCollectV4CommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCallLoadReportAndSyncAllReports()
    {
        $application = new Application($this->getKernel());
        $application->add(new PaymentReportSynchronizeAciCollectV4Command());

        $responseFromLoader = new PaymentProcessorReport();
        $responseFromLoader->addTransaction(new ReversalReportTransaction());
        $responseFromLoader->addTransaction(new ReversalReportTransaction());

        $loader = $this->getAciReportLoaderMock();
        $loader->expects($this->once())
            ->method('loadReport')
            ->will($this->returnValue($responseFromLoader));

        $this->getContainer()->set('payment_processor.aci.report_loader', $loader);

        $command = $application->find('payment:report:synchronize:aci:collectv4');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName()
            ]
        );

        $this->assertEquals('Amount of synchronized payments: 2', trim($commandTester->getDisplay()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AciReportLoader
     */
    protected function getAciReportLoaderMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportLoader',
            [],
            [],
            '',
            false
        );
    }
}
