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

        throw new ValidationEncoderException(
            sprintf('Invalid value "%s" for encoding.', $value)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function decode($encodedValue)
    {
        if ($this->isValidForDecryption($encodedValue) || $this->skipNotValid) {
            return $encodedValue;
        }

        throw new ValidationEncoderException(
            sprintf('Invalid value "%s" for decoding.', $encodedValue)
        );
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

    public function __set($name, $value)
    {
        $setter = 'set' . ucfirst($name);

        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }

        if (method_exists($this, 'get'.$name)) {
            throw new EncoderException(
                sprintf('Property "%s.%s" is read only.', get_class($this), $name)
            );
        } else {
            throw new EncoderException(
                sprintf('Property "%s.%s" is not defined.', get_class($this), $name)
            );
        }
    }
}
