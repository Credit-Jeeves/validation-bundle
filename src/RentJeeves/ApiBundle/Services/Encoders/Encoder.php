<?php

namespace RentJeeves\ApiBundle\Services\Encoders;


abstract class Encoder implements AttributeEncoderInterface
{
    protected $skipNotValid = false;
    /**
     * {@inheritdoc}
     */
    public function encode($value)
    {
        if ($this->isValidForEncryption($value) || $this->skipNotValid) {
            return $value;
        }

        throw new EncoderValidationException;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($encodedValue)
    {
        if ($this->isValidForDecryption($encodedValue) || $this->skipNotValid) {
            return $encodedValue;
        }

        throw new EncoderValidationException;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidForEncryption($value)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidForDecryption($encodedValue)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setSkipNotValid($isSkip)
    {
        $this->skipNotValid = $isSkip;

        return $this;
    }
}
