<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\DoD\Rule\PaymentRollingWindowDTR;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class PaymentRollingWindowDTRCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldReturnTrueIfGroupHasOrderAlgorithmTypeNotPayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $paymentRule = new PaymentRollingWindowDTR(
            $this->getOrderRepository(),
            0
        );
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s group has order algorithm not "pay_direct".'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfPaymentInRollingWindowAndPaymentsAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setStartDate('today');
        $paymentRule = new PaymentRollingWindowDTR(
            $this->getOrderRepository(new \DateTime('-2 days')),
            1
        );
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should return true if payment in rolling window and payment\'s group has order algorithm "pay_direct".'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfPaymentOutRollingWindowAndPaymentsAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $payment->setStartDate('today');
        $paymentRule = new PaymentRollingWindowDTR(
            $this->getOrderRepository(new \DateTime('-1 days')),
            1
        );
        $this->assertFalse(
            $paymentRule->checkPayment($payment),
            'Should return false if payment out rolling window and payment\'s group has order algorithm "pay_direct".'
        );
    }

    /**
     * @param \DateTime $orderCreatedAt
     * @return \CreditJeeves\DataBundle\Entity\OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderRepository($orderCreatedAt = null)
    {
        $orderCreatedAt = $orderCreatedAt ?: new \DateTime();
        $order = $this->getBaseMock('CreditJeeves\DataBundle\Entity\Order');
        $order->method('getCreatedAt')->willReturn($orderCreatedAt);

        $orderRepo = $this->getBaseMock('CreditJeeves\DataBundle\Entity\OrderRepository');
        $orderRepo->expects($this->any())->method('getLastDTRPaymentByContract')->willReturn($order);

        return $orderRepo;
    }
}
