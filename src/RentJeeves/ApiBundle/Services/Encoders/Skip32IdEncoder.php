<?php

namespace RentJeeves\ApiBundle\Services\Encoders;

use JMS\DiExtraBundle\Annotation as DI;
use Skip32 as Cipher;

/**
 * @DI\Service("api.id_obfuscator")
 */
class Skip32IdEncoder implements IdEncoderInterface
{
    const DEFAULT_KEY = '0123456789abcdef0123';

    protected $cipher;

    public function __construct($key = self::DEFAULT_KEY)
    {
        $this->cipher = new Cipher($key);
    }

    public function encode($integerId)
    {
        return $this->cipher->enc($integerId);
    }

    public function decode($encodedId)
    {
        return $this->cipher->dec($encodedId);
    }
}
