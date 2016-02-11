<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\DoD\Rule\PaymentExecutionAllowed;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class PaymentExecutionAllowedCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function disallowedPaymentDataProvider()
    {
        return [
            [false, PaymentAccepted::ANY, false],
            [false, PaymentAccepted::ANY],
            [false, PaymentAccepted::DO_NOT_ACCEPT],
            [false, PaymentAccepted::CASH_EQUIVALENT],
            [true, PaymentAccepted::DO_NOT_ACCEPT],
            [true, PaymentAccepted::CASH_EQUIVALENT],
        ];
    }

    /**
     * @test
     * @dataProvider disallowedPaymentDataProvider
     * @param bool $paymentAllowed
     * @param int $paymentAccepted
     * @param bool $isIntegrated
     */
    public function shouldReturnFalseIfPaymentIsNotAllowed($paymentAllowed, $paymentAccepted, $isIntegrated = true)
    {
        $payment = $this->preparePayment($paymentAllowed, $paymentAccepted, $isIntegrated);

        $paymentAllowedRule = new PaymentExecutionAllowed();
        $this->assertFalse(
            $paymentAllowedRule->checkPayment($payment),
            sprintf(
                'For contract.paymentAllowed = "%s" and contract.paymentAccepted = "%s" ' .
                'for %sintegrated group payment should be disallowed.',
                $paymentAllowed ? 'true' : 'false',
                $paymentAccepted,
                $isIntegrated ? '' : 'not '
            )
        );
    }

    public function allowedPaymentDataProvider()
    {
        return [
            [true, PaymentAccepted::ANY, false],
            [true, PaymentAccepted::DO_NOT_ACCEPT, false],
            [true, PaymentAccepted::CASH_EQUIVALENT, false],
            [true, PaymentAccepted::ANY],
        ];
    }

    /**
     * @test
     * @dataProvider allowedPaymentDataProvider
     * @param bool $paymentAllowed
     * @param int $paymentAccepted
     * @param bool $isIntegrated
     */
    public function shouldReturnTrueIfPaymentIsAllowed($paymentAllowed, $paymentAccepted, $isIntegrated = true)
    {
        $payment = $this->preparePayment($paymentAllowed, $paymentAccepted, $isIntegrated);

        $paymentAllowedRule = new PaymentExecutionAllowed();
        $this->assertTrue(
            $paymentAllowedRule->checkPayment($payment),
            sprintf(
                'For contract.paymentAllowed = "%s" and contract.paymentAccepted = "%s" ' .
                'for %sintegrated group payment should be allowed.',
                $paymentAllowed ? 'true' : 'false',
                $paymentAccepted,
                $isIntegrated ? '' : 'not '
            )
        );
    }

    /**
     * @param bool $paymentAllowed
     * @param int $paymentAccepted
     * @param bool $isIntegrated
     * @return Payment
     */
    protected function preparePayment($paymentAllowed, $paymentAccepted, $isIntegrated = true)
    {
        $group = new Group();
        $group->getGroupSettings()->setIsIntegrated($isIntegrated);

        $contract = new Contract();
        $contract->setPaymentAllowed($paymentAllowed);
        $contract->setPaymentAccepted($paymentAccepted);
        $contract->setGroup($group);

        $payment = new Payment();
        $payment->setContract($contract);

        return $payment;
    }
}
