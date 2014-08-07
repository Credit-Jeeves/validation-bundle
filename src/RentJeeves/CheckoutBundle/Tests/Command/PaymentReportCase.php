<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PaymentReportCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\DataBundle\Entity\Heartland;

class PaymentReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executeCommand()
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
        $this->assertCount(4, $count);
        $this->assertContains('Amount of synchronized payments: 7', $commandTester->getDisplay());
    }

    /**
     * @test
     * @depends executeCommand
     */
    public function voidCCPayment()
    {
        $originalTransId = 258258;
        $voidTransId = 258259;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Heartland $originalTransaction */
        $originalTransaction = $em->getRepository('RjDataBundle:Heartland')->findOneByTransactionId($originalTransId);
        $this->assertNotNull($originalTransaction);
        /** @var Heartland $voidTransaction */
        $voidTransaction = $em->getRepository('RjDataBundle:Heartland')->findOneByTransactionId($voidTransId);
        $this->assertNotNull($voidTransaction);
        $this->assertNull($originalTransaction->getDepositDate());
        $this->assertEquals(0, $originalTransaction->getAmount() + $voidTransaction->getAmount());
        $this->assertSame($originalTransaction->getOrder(), $voidTransaction->getOrder());
        $this->assertEquals(OrderStatus::CANCELLED, $originalTransaction->getOrder()->getStatus());
    }
}
