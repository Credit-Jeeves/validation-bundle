<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;


interface DodRuleInterface
{
    /**
     * Returns a reason why the check failed.
     *
     * @return string
     */
    public function getReason();

    /**
     * Check that rule can checked this object
     *
     * @param $object
     * @return boolean
     */
    public function support($object);
}
