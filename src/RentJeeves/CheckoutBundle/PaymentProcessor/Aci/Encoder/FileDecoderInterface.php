<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Encoder;

interface FileDecoderInterface
{
    /**
     * Decodes file data
     *
     * @param string $fileName path to decoded file
     *
     * @return mixed
     */
    public function decode($fileName);
}
