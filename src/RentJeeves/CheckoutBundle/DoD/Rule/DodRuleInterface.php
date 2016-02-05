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
     * Verifies if a given rule can check a passed object
     *
     * @param $object
     * @return boolean
     */
    public function support($object);
}
