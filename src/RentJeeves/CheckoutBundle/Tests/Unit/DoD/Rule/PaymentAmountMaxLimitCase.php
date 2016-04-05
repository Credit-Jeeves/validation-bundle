<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\DoD\Rule\PaymentAmountMaxLimit;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class PaymentAmountMaxLimitCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnFalseIfPaymentAmountMoreThanLimitForGroupWithOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);

        $contract = new Contract();
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setAmount(501);

        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertFalse(
            $limitRule->checkPayment($payment),
            '501 exceeds 500 and group has order algorithm "pay_direct", check should return false'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfPaymentAmountAllowedByLimitForGroupWithOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);

        $contract = new Contract();
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setAmount(500);

        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertTrue(
            $limitRule->checkPayment($payment),
            '500 doesn\'t exceed 500 and group has order algorithm "pay_direct", should return true'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfPaymentAmountMoreThanLimitForGroupWithOrderAlgorithmTypeNotPayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);

        $contract = new Contract();
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setAmount(501);

        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertTrue(
            $limitRule->checkPayment($payment),
            '501 exceeds 500 and group has order algorithm no "pay_direct", check should return true'
        );
    }

    /**
     * @test
     */
    public function shouldReturnReasonMessage()
    {
        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertEquals($limitRule->getReasonMessage(), 'Payment amount exceeds MAX limit of 500');
    }

    /**
     * @test
     */
    public function shouldReturnReasonCode()
    {
        $limitRule = new PaymentAmountMaxLimit(500);
        $this->assertEquals($limitRule->getReasonCode(), PaymentFlaggedReason::AMOUNT_LIMIT_EXCEEDED);
    }
}
