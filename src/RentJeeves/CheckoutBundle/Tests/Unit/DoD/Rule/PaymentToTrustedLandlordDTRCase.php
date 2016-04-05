<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\DoD\Rule\PaymentToTrustedLandlordDTR;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class PaymentToTrustedLandlordDTRCase extends UnitTestBase
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
        $paymentRule = new PaymentToTrustedLandlordDTR($this->getMailerMock());
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s group has order algorithm not "pay_direct".'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfGroupDoesNotHaveTrustedLandlord()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $mailerMock = $this->getMailerMock();
        $mailerMock
            ->expects($this->once())
            ->method('sendEmailPaymentFlaggedByUntrustedLandlordRule');
        $paymentRule = new PaymentToTrustedLandlordDTR($mailerMock);
        $this->assertFalse(
            $paymentRule->checkPayment($payment),
            'Should return false if payment\'s group dtr and does not have trusted landlord.'
        );
    }

    /**
     * @return array
     */
    public function untrustedLandlordStatuses()
    {
        return [
            [TrustedLandlordStatus::NEWONE],
            [TrustedLandlordStatus::RFI],
            [TrustedLandlordStatus::DENIED],
        ];
    }

    /**
     * @param string $untrustedStatus
     *
     * @test
     * @dataProvider untrustedLandlordStatuses
     */
    public function shouldReturnFalseIfGroupHasTrustedLandlordWithUntrustedStatus($untrustedStatus)
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus($untrustedStatus);
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->setTrustedLandlord($trustedLandlord);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $mailerMock = $this->getMailerMock();
        $mailerMock
            ->expects($this->once())
            ->method('sendEmailPaymentFlaggedByUntrustedLandlordRule');
        $paymentRule = new PaymentToTrustedLandlordDTR($mailerMock);
        $this->assertFalse(
            $paymentRule->checkPayment($payment),
            'Should return false if payment\'s group dtr and has trusted landlord without trusted status.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfGroupHasTrustedLandlordWithTrustedStatus()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::TRUSTED);
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->setTrustedLandlord($trustedLandlord);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $mailerMock = $this->getMailerMock();
        $mailerMock
            ->expects($this->never())
            ->method('sendEmailPaymentFlaggedByUntrustedLandlordRule');
        $paymentRule = new PaymentToTrustedLandlordDTR($mailerMock);
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should return true if payment\'s group dtr and has trusted landlord with trusted status.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnReasonCode()
    {
        $paymentRule = new PaymentToTrustedLandlordDTR($this->getMailerMock());
        $this->assertEquals($paymentRule->getReasonCode(), PaymentFlaggedReason::DTR_UNTRUSTED_LANDLORD);
    }
}
