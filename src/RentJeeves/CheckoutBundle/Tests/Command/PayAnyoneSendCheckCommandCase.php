<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Command\PayAnyoneSendCheckCommand;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PayAnyoneSendCheckCommandCase extends BaseTestCase
{
    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @var \Symfony\Component\Console\Command\Command
     */
    protected $command;

    /**
     * @return CommandTester
     */
    protected function getCommandTester()
    {
        if (is_null($this->commandTester)) {
            $this->commandTester = new CommandTester($this->getCommand());
        }

        return $this->commandTester;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function getCommand()
    {
        if (is_null($this->command)) {
            $application = new Application($this->getKernel());
            $application->add(new PayAnyoneSendCheckCommand());

            $this->command = $application->find('payment:pay-anyone:send-check');
        }

        return $this->command;
    }

    /**
     * @return OrderPayDirect $order
     */
    protected function preparePayDirectOrder()
    {
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(23);

        $this->assertNotEmpty($contract, 'Please check fixtures');

        $order = new OrderPayDirect();
        $order->setUser($contract->getTenant());
        $order->setStatus(OrderStatus::SENDING);
        $order->setPaymentType(OrderPaymentType::BANK);
        $order->setSum(600);
        $order->setPaymentProcessor(PaymentProcessor::ACI);
        $order->setDescriptor('Test Check');

        $operation = new Operation();
        $operation->setAmount(600);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setGroup($contract->getGroup());
        $operation->setContract($contract);
        $operation->setPaidFor(new \DateTime());

        $order->addOperation($operation);

        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();

        return $order;
    }

    /**
     * @return OrderSubmerchant $order
     */
    protected function prepareSubmerchantOrder()
    {
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(23);

        $this->assertNotEmpty($contract, 'Please check fixtures');

        $order = new OrderSubmerchant();

        $order->setStatus(OrderStatus::SENDING);
        $order->setPaymentType(OrderPaymentType::CASH);
        $order->setUser($contract->getTenant());
        $order->setSum(600);
        $order->setPaymentProcessor(PaymentProcessor::ACI);

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();

        return $order;
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Order with id#12345678 not found
     */
    public function shouldThrowExceptionIfOrderNotFound()
    {
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--jms-job-id' => 1,
                'order-id' => 12345678
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldWorkWithPayDirectOrdersOnly()
    {
        $order = $this->prepareSubmerchantOrder();

        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--jms-job-id' => 1,
                'order-id' => $order->getId()
            ]
        );
    }

    /**
     * @test
     */
    public function shouldSetErrorStatus()
    {
        $order = $this->preparePayDirectOrder();

        $order->setSum(-100);

        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--jms-job-id' => 1,
                'order-id' => $order->getId()
            ]
        );

        $this->getEntityManager()->refresh($order);
        $this->assertEquals(OrderStatus::ERROR, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldNotChangeOrderStatusIfSendingCheckIsSuccessful()
    {
        $order = $this->preparePayDirectOrder();

        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--jms-job-id' => 1,
                'order-id' => $order->getId()
            ]
        );

        $this->getEntityManager()->refresh($order);
        $this->assertEquals(OrderStatus::SENDING, $order->getStatus());
    }
}
