<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use RentJeeves\DataBundle\Entity\Payment;

interface DodRuleInterface
{
    /**
     * Checks a given payment and return true if the check passed.
     *
     * @param Payment $payment
     * @return bool
     */
    public function checkPayment(Payment $payment);

    /**
     * Returns a reason why the check failed.
     *
     * @return string
     */
    public function getReason();
}
