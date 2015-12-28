<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CheckoutBundle\DoD\DodManager;
use RentJeeves\CheckoutBundle\DoD\Rule\DodRuleInterface;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class DoDManagerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldCheckPaymentAndReturnTrueIfPaymentRuleReturnsTrue()
    {
        $rule = $this->getRuleMock();
        $rule
            ->expects($this->once())
            ->method('checkPayment')
            ->will($this->returnValue(true));

        $dodManager = new DodManager($this->getLoggerMock());
        $dodManager->addRule($rule);

        $this->assertTrue($dodManager->checkPayment(new Payment()), 'DoD manager should return true if rule is valid');
    }

    /**
     * @test
     */
    public function shouldCheckPaymentAndSetStatusToFlaggedAndLogAlertAndReturnFalseIfPaymentRuleReturnsFalse()
    {
        $logger = $this->getLoggerMock();
        $logger
            ->expects($this->once())
            ->method('alert');

        $rule = $this->getRuleMock();
        $rule
            ->expects($this->once())
            ->method('checkPayment')
            ->will($this->returnValue(false));
        $rule
            ->expects($this->once())
            ->method('getReason')
            ->will($this->returnValue('Reason to be flagged'));

        $payment = new Payment();
        $tenant = new Tenant();
        $tenant->setEmail('tenant@email.com');
        $contract = new Contract();
        $contract->setTenant($tenant);
        $group = new Group();
        $group->setHolding(new Holding());
        $depositAccount = new DepositAccount($group);
        $depositAccount->setType(DepositAccountType::APPLICATION_FEE);
        $payment->setContract($contract);
        $payment->setDepositAccount($depositAccount);
        $payment->setTotal(10000);
        $payment->setType(PaymentType::ONE_TIME);
        $payment->setStatus(PaymentStatus::ACTIVE);

        $dodManager = new DodManager($logger);
        $dodManager->addRule($rule);

        $this->assertFalse($dodManager->checkPayment($payment), 'DoD manager should return false if rule is invalid');
        $this->assertEquals(PaymentStatus::FLAGGED, $payment->getStatus(), 'DoD manager should set status to FLAGGED');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DodRuleInterface
     */
    protected function getRuleMock()
    {
        return $this->getMock('\RentJeeves\CheckoutBundle\DoD\Rule\DodRuleInterface', [], [], '', false);
    }
}
