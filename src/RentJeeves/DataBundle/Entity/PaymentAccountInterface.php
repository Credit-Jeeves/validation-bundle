<?php

namespace RentJeeves\DataBundle\Entity;

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
