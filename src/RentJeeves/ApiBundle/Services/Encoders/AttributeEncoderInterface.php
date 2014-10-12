<?php

namespace RentJeeves\ApiBundle\Services\Encoders;

interface AttributeEncoderInterface
{
    /**
     * @param $value
     * @return mixed Encoded Value
     */
    public function encode($value);

    /**
     * @param $encodedValue
     * @return mixed Decoded Value
     */
    public function decode($encodedValue);
}
