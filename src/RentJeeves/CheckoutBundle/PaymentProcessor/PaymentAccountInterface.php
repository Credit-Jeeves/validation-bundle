<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

interface PaymentAccountInterface
{
    /**
     * @return string
     */
    public function getToken();

    /**
     * @return string
     * @see PaymentAccountType "bank" |"card"
     */
    public function getType();
}
