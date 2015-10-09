<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\Services;

use RentJeeves\CheckoutBundle\Services\PaymentAmountLimit;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\TestBundle\BaseTestCase;

class PaymentAmountLimitCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnTrueIfPaymentAmountExceedsLimit()
    {
        $payment = new Payment();
        $payment->setAmount(501);

        $limitService = new PaymentAmountLimit(500, $this->getLoggerMock());
        $this->assertTrue($limitService->checkIfExceedsMax($payment), '501 exceeds 500, should return true');
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfPaymentAmountDoesNotExceedLimit()
    {
        $payment = new Payment();
        $payment->setAmount(500);

        $limitService = new PaymentAmountLimit(500, $this->getLoggerMock());
        $this->assertFalse($limitService->checkIfExceedsMax($payment), '500 doesn\'t exceed 500, should return false');
    }

    /**
     * @test
     */
    public function shouldLogAlertIfPaymentAmountExceedsLimitAndIsAlertTrue()
    {
        $logger = $this->getLoggerMock();
        $logger
            ->expects($this->exactly(1))
            ->method('alert');

        $payment = new Payment();
        $payment->setAmount(600);

        $limitService = new PaymentAmountLimit(500, $logger);
        $this->assertTrue($limitService->checkIfExceedsMax($payment, true), '501 exceeds 500, should return true');
    }

    /**
     * @test
     */
    public function shouldNotLogAlertIfPaymentAmountExceedsLimitAndIsAlertFalse()
    {
        $logger = $this->getLoggerMock();
        $logger
            ->expects($this->exactly(0))
            ->method('alert');

        $payment = new Payment();
        $payment->setAmount(600);

        $limitService = new PaymentAmountLimit(500, $logger);
        $this->assertTrue($limitService->checkIfExceedsMax($payment, false), '501 exceeds 500, should return true');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }
}
