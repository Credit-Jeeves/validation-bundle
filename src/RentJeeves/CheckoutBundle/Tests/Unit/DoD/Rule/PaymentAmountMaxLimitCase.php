<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use RentJeeves\CheckoutBundle\DoD\Rule\PaymentAmountMaxLimit;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class PaymentAmountMaxLimitCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnFalseIfPaymentAmountMoreThanLimit()
    {
        $payment = new Payment();
        $payment->setAmount(501);

        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertFalse($limitRule->checkPayment($payment), '501 exceeds 500, check should return false');
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfPaymentAmountAllowedByLimit()
    {
        $payment = new Payment();
        $payment->setAmount(500);

        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertTrue($limitRule->checkPayment($payment), '500 doesn\'t exceed 500, should return true');
    }

    /**
     * @test
     */
    public function shouldReturnReason()
    {
        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertEquals($limitRule->getReason(), 'Payment amount exceeds MAX limit of 500');
    }
}
