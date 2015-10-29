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

    /**
     * Means can be encoded
     * @param $value
     * @return bool
     */
    public function isValidForEncryption($value);

    /**
     * Means can be decoded
     * @param $encodedValue
     * @return bool
     */
    public function isValidForDecryption($encodedValue);

    /**
     * $sipNotValid default = true
     * @param bool $isSkip
     * @return self
     */
    public function setSkipNotValid($isSkip);
}
