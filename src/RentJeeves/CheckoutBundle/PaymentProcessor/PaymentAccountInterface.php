<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

interface PaymentAccountInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getToken();

    /**
     * @return string
     * @see RentJeeves\DataBundle\Enum\PaymentAccountType "bank" |"card"
     */
    public function getType();

    /**
     * @return string
     * @see RentJeeves\DataBundle\Enum\BankAccountType
     */
    public function getBankAccountType();
}
