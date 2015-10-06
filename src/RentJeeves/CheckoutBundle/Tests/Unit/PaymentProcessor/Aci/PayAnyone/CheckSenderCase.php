<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\PayAnyone;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderPayDirectStatusManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\CheckSender;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class CheckSenderCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldCreateObjectReportSynchronizer()
    {
        return new CheckSender(
            $this->getPayDirectProcessorMock(),
            $this->getOrderStatusManagerMock(),
            $this->getLoggerMock()
        );
    }

    /**
     * @test
     */
    public function shouldUpdateOrderStatusAndReturnTrueIfOrderIsExecutedSuccessfully()
    {
        $order = new OrderPayDirect();
        $this->writeIdAttribute($order, 1);

        $paymentProcessorMock = $this->getPayDirectProcessorMock();
        $paymentProcessorMock->expects($this->once())
            ->method('executeOrder')
            ->with($this->equalTo($order))
            ->will($this->returnValue(true));

        $orderStatusManagerMock = $this->getOrderStatusManagerMock();
        $orderStatusManagerMock->expects($this->once())
            ->method('setSending')
            ->with($this->equalTo($order));

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('debug');

        $sender = new CheckSender(
            $paymentProcessorMock,
            $orderStatusManagerMock,
            $loggerMock
        );

        $this->assertTrue($sender->send($order), 'Check for OrderPayDirect has not been sent successfully');
    }

    /**
     * @test
     */
    public function shouldLogEmergencyAndReturnFalseIfOrderIsExecutedWithException()
    {
        $order = new OrderPayDirect();
        $this->writeIdAttribute($order, 1);

        $paymentProcessorMock = $this->getPayDirectProcessorMock();
        $paymentProcessorMock->expects($this->once())
            ->method('executeOrder')
            ->with($this->equalTo($order))
            ->will($this->throwException(new \Exception('test')));

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('emergency');

        $sender = new CheckSender(
            $paymentProcessorMock,
            $this->getOrderStatusManagerMock(),
            $loggerMock
        );

        $this->assertFalse($sender->send($order), 'Check for OrderPayDirect has been sent successfully');
    }

    /**
     * @test
     */
    public function shouldUpdateOrderStatusToErrorAndReturnFalseIfOrderIsExecutedNotSuccessfully()
    {
        $order = new OrderPayDirect();
        $this->writeIdAttribute($order, 1);

        $outboundTransaction = new OutboundTransaction();
        $outboundTransaction->setMessage('test');
        $outboundTransaction->setType(OutboundTransactionType::DEPOSIT);

        $order->addOutboundTransaction($outboundTransaction);

        $paymentProcessorMock = $this->getPayDirectProcessorMock();
        $paymentProcessorMock->expects($this->once())
            ->method('executeOrder')
            ->with($this->equalTo($order))
            ->will($this->returnValue(false));

        $orderStatusManagerMock = $this->getOrderStatusManagerMock();
        $orderStatusManagerMock->expects($this->once())
            ->method('setError')
            ->with($this->equalTo($order));

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('alert');

        $sender = new CheckSender(
            $paymentProcessorMock,
            $orderStatusManagerMock,
            $loggerMock
        );

        $this->assertFalse($sender->send($order), 'Check for OrderPayDirect has been sent successfully');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|OrderPayDirectStatusManager
     */
    protected function getOrderStatusManagerMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderPayDirectStatusManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentProcessorAciPayAnyone
     */
    protected function getPayDirectProcessorMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone',
            [],
            [],
            '',
            false
        );
    }
}
