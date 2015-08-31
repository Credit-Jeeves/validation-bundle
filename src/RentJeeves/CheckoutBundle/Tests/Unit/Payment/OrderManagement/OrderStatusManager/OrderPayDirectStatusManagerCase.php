<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderPayDirectStatusManager;
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
    public function shouldSetOrderToSendingWhenOutboundLegIsNotInitiated()
    {
        $order = new OrderPayDirect();

        $this->statusManager->setComplete($order);

        $this->assertEquals(OrderStatus::SENDING, $order->getStatus());
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
}
