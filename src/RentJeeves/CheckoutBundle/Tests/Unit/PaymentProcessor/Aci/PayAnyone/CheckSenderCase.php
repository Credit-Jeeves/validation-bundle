<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\PayAnyone;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
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
            $this->getLoggerMock()
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfOrderIsExecutedSuccessfully()
    {
        $order = new OrderPayDirect();
        $this->writeIdAttribute($order, 1);

        $paymentProcessorMock = $this->getPayDirectProcessorMock();
        $paymentProcessorMock->expects($this->once())
            ->method('executeOrder')
            ->with($this->equalTo($order))
            ->will($this->returnValue(true));

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('debug');

        $sender = new CheckSender(
            $paymentProcessorMock,
            $loggerMock
        );

        $this->assertTrue($sender->send($order), 'Check for OrderPayDirect has not been sent successfully');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Exception\CheckSenderException
     */
    public function shouldThrowExceptionIfOrderIsExecutedWithException()
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
            $loggerMock
        );

        $sender->send($order);
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfOrderIsExecutedNotSuccessfully()
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

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('alert');

        $sender = new CheckSender(
            $paymentProcessorMock,
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
