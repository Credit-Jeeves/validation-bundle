<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderPayDirectStatusManager;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionStatus;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;
use RentJeeves\TestBundle\Mocks\CommonSystemMocks;

class OrderPayDirectStatusManagerCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderPayDirectStatusManager
     */
    protected $statusManager;

    protected function setUp()
    {
        $systemsMocks = new CommonSystemMocks();

        $this->statusManager = new OrderPayDirectStatusManager(
            $systemsMocks->getEntityManagerMock(),
            $systemsMocks->getLoggerMock(),
            $systemsMocks->getMailerMock()
        );
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsupported order type in OrderPayDirectStatusManager
     */
    public function shouldAssertOrderTypeWhenSetComplete()
    {
        $order = new OrderSubmerchant();
        $this->statusManager->setComplete($order);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsupported order type in OrderPayDirectStatusManager
     */
    public function shouldAssertOrderTypeWhenSetReturned()
    {
        $order = new OrderSubmerchant();
        $this->statusManager->setReturned($order);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsupported order type in OrderPayDirectStatusManager
     */
    public function shouldAssertOrderTypeWhenSetRefunded()
    {
        $order = new OrderSubmerchant();
        $this->statusManager->setRefunded($order);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsupported order type in OrderPayDirectStatusManager
     */
    public function shouldAssertOrderTypeWhenSetReissued()
    {
        $order = new OrderSubmerchant();
        $this->statusManager->setReissued($order);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsupported order type in OrderPayDirectStatusManager
     */
    public function shouldAssertOrderTypeWhenSetSending()
    {
        $order = new OrderSubmerchant();
        $this->statusManager->setSending($order);
    }

    /**
     * @test
     */
    public function shouldSetOrderToCompleteWhenOutboundLegIsInitiated()
    {
        $order = new OrderPayDirect();
        $transaction = new OutboundTransaction();
        $transaction->setType(OutboundTransactionType::DEPOSIT);
        $transaction->setStatus(OutboundTransactionStatus::SUCCESS);
        $order->addOutboundTransaction($transaction);

        $this->statusManager->setComplete($order);

        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldCreateJobForSendingCheckWhenSetOrderToCompleteAndOutboundLegIsNotInitiated()
    {
        $emMock = $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false);
        $job = new Job('payment:pay-anyone:send-check', ['--app=rj', null]);
        $emMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($job));
        $emMock
            ->expects($this->once())
            ->method('flush')
            ->with($this->equalTo($job));

        $statusManager = new OrderPayDirectStatusManager(
            $emMock,
            $this->getMock('\Monolog\Logger', [], [], '', false),
            $this->getMock('RentJeeves\CoreBundle\Mailer\Mailer', [], [], '', false)
        );

        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::PENDING);

        $statusManager->setComplete($order);

        $this->assertEquals(OrderStatus::PENDING, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldNotChangeOrderStatusWhenSetOrderToCancelledAndOutboundLegIsInitiated()
    {
        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::NEWONE);
        $transaction = new OutboundTransaction();
        $transaction->setType(OutboundTransactionType::DEPOSIT);
        $transaction->setStatus(OutboundTransactionStatus::SUCCESS);
        $order->addOutboundTransaction($transaction);

        $this->statusManager->setCancelled($order);

        $this->assertEquals(OrderStatus::NEWONE, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSetOrderToCancelledWhenOutboundLegIsNotInitiated()
    {
        $order = new OrderPayDirect();

        $this->statusManager->setCancelled($order);

        $this->assertEquals(OrderStatus::CANCELLED, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSetOrderToRefundedWhenOutboundLegIsReversed()
    {
        $order = new OrderPayDirect();
        $transaction = new OutboundTransaction();
        $transaction->setType(OutboundTransactionType::REVERSAL);
        $order->addOutboundTransaction($transaction);

        $this->statusManager->setRefunded($order);

        $this->assertEquals(OrderStatus::REFUNDED, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSetOrderToRefundingWhenOutboundLegIsNotReversed()
    {
        $order = new OrderPayDirect();
        $transaction = new OutboundTransaction();
        $transaction->setType(OutboundTransactionType::DEPOSIT);
        $transaction->setStatus(OutboundTransactionStatus::SUCCESS);
        $order->addOutboundTransaction($transaction);

        $this->statusManager->setRefunded($order);

        $this->assertEquals(OrderStatus::REFUNDING, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSetOrderToReissued()
    {
        $order = new OrderPayDirect();

        $this->statusManager->setReissued($order);

        $this->assertEquals(OrderStatus::REISSUED, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSetOrderToReturned()
    {
        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::NEWONE);
        $this->statusManager->setReturned($order);
        $this->assertEquals(OrderStatus::RETURNED, $order->getStatus());

        $order->setStatus(OrderStatus::SENDING);
        $this->statusManager->setReturned($order);
        $this->assertEquals(OrderStatus::RETURNED, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSetOrderToSending()
    {
        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::NEWONE);

        $this->statusManager->setSending($order);

        $this->assertEquals(OrderStatus::SENDING, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldNotChangeOrderStatusWhenSetToRefundedIfOrderIsReturned()
    {
        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::RETURNED);

        $this->statusManager->setRefunded($order);

        $this->assertEquals(OrderStatus::RETURNED, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSendEmailWhenSetOrderStatusToSending()
    {
        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::NEWONE);

        $mailerMock = $this->getMock('RentJeeves\CoreBundle\Mailer\Mailer', [], [], '', false);
        $mailerMock
            ->expects($this->once())
            ->method('sendOrderSendingNotification')
            ->with($order);

        $statusManager = new OrderPayDirectStatusManager(
            $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false),
            $this->getMock('\Monolog\Logger', [], [], '', false),
            $mailerMock
        );

        $statusManager->setSending($order);
        $this->assertEquals(OrderStatus::SENDING, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSendEmailWhenSetOrderStatusToRefunding()
    {
        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::NEWONE);

        $mailerMock = $this->getMock('RentJeeves\CoreBundle\Mailer\Mailer', [], [], '', false);
        $mailerMock
            ->expects($this->once())
            ->method('sendOrderRefundingNotification')
            ->with($order);

        $statusManager = new OrderPayDirectStatusManager(
            $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false),
            $this->getMock('\Monolog\Logger', [], [], '', false),
            $mailerMock
        );

        $statusManager->setRefunded($order);
        $this->assertEquals(OrderStatus::REFUNDING, $order->getStatus());
    }

    /**
     * @test
     */
    public function shouldSendEmailWhenSetOrderStatusToReissued()
    {
        $order = new OrderPayDirect();
        $order->setStatus(OrderStatus::NEWONE);

        $mailerMock = $this->getMock('RentJeeves\CoreBundle\Mailer\Mailer', [], [], '', false);
        $mailerMock
            ->expects($this->once())
            ->method('sendOrderReissuedNotification')
            ->with($order);

        $statusManager = new OrderPayDirectStatusManager(
            $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false),
            $this->getMock('\Monolog\Logger', [], [], '', false),
            $mailerMock
        );

        $statusManager->setReissued($order);
        $this->assertEquals(OrderStatus::REISSUED, $order->getStatus());
    }
}
