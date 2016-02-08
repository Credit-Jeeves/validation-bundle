<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use RentJeeves\DataBundle\Entity\Payment;

interface DodPaymentRuleInterface extends DodRuleInterface
{
    /**
     * Checks a given payment and return true if the check passed.
     *
     * @param Payment $payment
     * @return bool
     */
    public function checkPayment(Payment $payment);
}
