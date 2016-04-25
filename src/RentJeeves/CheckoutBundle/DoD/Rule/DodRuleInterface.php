<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;


interface DodRuleInterface
{
    /**
     * Returns a reason why the check failed.
     * @see RentJeeves\DataBundle\Enum\PaymentFlaggedReason
     *
     * @return string
     */
    public function getReasonCode();

    /**
     * Returns a reason why the check failed, with details.
     *
     * @return string
     */
    public function getReasonMessage();

    /**
     * Verifies if a given rule can check a passed object
     *
     * @param $object
     * @return boolean
     */
    public function support($object);
}
