<?php

namespace RentJeeves\ApiBundle\Services\Encoders;

use JMS\DiExtraBundle\Annotation as DI;
use Skip32 as Cipher;

/**
 * @DI\Service("skip32.id_encoder")
 */
class Skip32IdEncoder extends Encoder
{
    const DEFAULT_KEY = '0123456789abcdef0123';

    protected $cipher;

    /**
     * @param string $key
     * @param bool   $skipNotValid
     */
    public function __construct($key = self::DEFAULT_KEY, $skipNotValid = false)
    {
        $this->cipher = new Cipher($key);
        $this->skipNotValid = $skipNotValid;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($integerId)
    {
        if ($this->isValidForEncryption($integerId)) {
            return $this->cipher->enc($integerId);
        } elseif ($this->skipNotValid) {
            return $integerId;
        }

        throw new ValidationEncoderException(
            sprintf('Value "%s" isn\'t correct integer Id.', $integerId)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function decode($encodedId)
    {
        if ($this->isValidForDecryption($encodedId)) {
            return $this->cipher->dec($encodedId);
        } elseif ($this->skipNotValid) {
            return $encodedId;
        }

        throw new ValidationEncoderException(
            sprintf('Value "%s" isn\'t correct encrypted Id.', $encodedId)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isValidForEncryption($value)
    {
        return $this->validate($value);
    }

    /**
     * {@inheritdoc}
     */
    public function isValidForDecryption($encodedValue)
    {
        return $this->validate($encodedValue);
    }

    protected function validate($value)
    {
        if (is_int($value)) {
            return true;
        }

        if (is_numeric($value)) {
            $int = (int) $value;

            return (strlen($value) === strlen($int) and $int == $value);
        }

        return false;
    }
}
