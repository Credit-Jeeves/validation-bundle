<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\DoD\Rule\PaymentFirstTimeDTR;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class PaymentFirstTimeDTRCase extends UnitTestBase
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

        $paymentRule = new PaymentFirstTimeDTR(
            $this->getPidkiqRepository(),
            $this->getPaymentRepository(),
            $this->getOrderRepository()
        );

        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s group has order algorithm not "pay_direct".'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfContractAlreadyHasPaymentsAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);

        $contract = new Contract();
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);

        $paymentRule = new PaymentFirstTimeDTR(
            $this->getPidkiqRepository(),
            $this->getPaymentRepository(1),
            $this->getOrderRepository(0)
        );

        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should return true if payment\'s group group has order algorithm "pay_direct" and contract has payments.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfContractAlreadyHasOrdersAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);

        $contract = new Contract();
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);

        $paymentRule = new PaymentFirstTimeDTR(
            $this->getPidkiqRepository(),
            $this->getPaymentRepository(0),
            $this->getOrderRepository(1)
        );

        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should return true if payment\'s group group has order algorithm "pay_direct" and contract has orders.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfContractAlreadyHasOrdersAndPaymentsAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);

        $contract = new Contract();
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);

        $paymentRule = new PaymentFirstTimeDTR(
            $this->getPidkiqRepository(),
            $this->getPaymentRepository(1),
            $this->getOrderRepository(1)
        );

        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should return true if payment\'s group group has order algorithm "pay_direct"' .
            ' and contract has orders and payments.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfContractHasNorOrdersNeitherPaymentsAndOrderAlgorithmTypePayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);

        $contract = new Contract();
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);

        $paymentRule = new PaymentFirstTimeDTR(
            $this->getPidkiqRepository(),
            $this->getPaymentRepository(0),
            $this->getOrderRepository(0)
        );

        $this->assertFalse(
            $paymentRule->checkPayment($payment),
            'Should return false if payment\'s group group has order algorithm "pay_direct"' .
            ' and contract has nor orders neither payments.'
        );
    }

    /**
     * @param int $countOrders
     * @return \CreditJeeves\DataBundle\Entity\OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderRepository($countOrders = 1)
    {
        $orderRepo = $this->getBaseMock('CreditJeeves\DataBundle\Entity\OrderRepository');

        $orderRepo->expects($this->any())->method('countOrdersByContract')->willReturn($countOrders);

        return $orderRepo;
    }

    /**
     * @param int $countPayments
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\DataBundle\Entity\PaymentRepository
     */
    protected function getPaymentRepository($countPayments = 1)
    {
        $paymentRepo = $this->getBaseMock('RentJeeves\DataBundle\Entity\PaymentRepository');

        $paymentRepo->expects($this->any())->method('countPaymentsByContract')->willReturn($countPayments);

        return $paymentRepo;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\PidkiqRepository
     */
    protected function getPidkiqRepository()
    {
        return $this->getBaseMock('CreditJeeves\DataBundle\Entity\PidkiqRepository');
    }
}
