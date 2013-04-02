<?php
namespace CreditJeeves\UserBundle\Security\Encoder;

use Symfony\Component\Security\Core\Encoder;


class MessageDigestPasswordEncoder extends Encoder\BasePasswordEncoder
{
    public function encodePassword($raw, $salt)
    {
        return md5($raw);
    }

    /**
     * Checks a raw password against an encoded password.
     *
     * @param string $encoded An encoded password
     * @param string $raw     A raw password
     * @param string $salt    The salt
     *
     * @return Boolean true if the password is valid, false otherwise
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded == $this->encodePassword($raw, $salt);
    }
}
