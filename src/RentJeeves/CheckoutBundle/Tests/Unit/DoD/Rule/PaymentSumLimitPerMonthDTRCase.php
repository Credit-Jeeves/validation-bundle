<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\DoD\Rule\PaymentSumLimitPerMonthDTR;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class PaymentSumLimitPerMonthDTRCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldReturnTrueIfGroupHasOrderAlgorithmTypeNotPayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $group->getGroupSettings()->setMaxLimitPerMonth(100);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setTotal(100);
        $paymentRule = new PaymentSumLimitPerMonthDTR($this->getOperationRepository(100));
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s group has order algorithm not "pay_direct".'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfGroupHasUnlimitedPaymentsAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->getGroupSettings()->setMaxLimitPerMonth(0); // unlimited
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setTotal(100);
        $paymentRule = new PaymentSumLimitPerMonthDTR($this->getOperationRepository(100));
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s group has unlimited payments.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfMonthlySumLessThenLimitAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->getGroupSettings()->setMaxLimitPerMonth(201);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setTotal(100);
        $paymentRule = new PaymentSumLimitPerMonthDTR($this->getOperationRepository(100));
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s sum limit for current month less then limited.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfMonthlySumEqualsLimitAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->getGroupSettings()->setMaxLimitPerMonth(200);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setTotal(100);
        $paymentRule = new PaymentSumLimitPerMonthDTR($this->getOperationRepository(100));
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s sum limit for current month equals limited.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfMonthlySumMoreThenLimitAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->getGroupSettings()->setMaxLimitPerMonth(100);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setTotal(100);
        $paymentRule = new PaymentSumLimitPerMonthDTR($this->getOperationRepository(1));
        $this->assertFalse(
            $paymentRule->checkPayment($payment),
            'Should return false if payment\'s sum limit for current month more then limited.'
        );
    }

    /**
     * @param int $sum
     * @return \CreditJeeves\DataBundle\Entity\OperationRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOperationRepository($sum = 0)
    {
        $operationRepo = $this->getBaseMock('CreditJeeves\DataBundle\Entity\OperationRepository');
        $operationRepo->expects($this->any())->method('getSumPaymentsByGroupInDateMonth')->willReturn($sum);

        return $operationRepo;
    }
}
