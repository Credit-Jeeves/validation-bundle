<?php

namespace RentJeeves\ApiBundle\Forms\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use stdClass;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(groups={"card"}, methods={"isValid"})
 */
class Card
{
    /**
     * @Assert\NotBlank(groups={"card"})
     * @Assert\Regex(
     *      pattern="/^[0-9]{13,16}$/",
     *      message="api.errors.payment_accounts.card.account.invalid_format",
     *      groups={"card"}
     * )
     */
    public $account;

    /**
     * @Assert\NotBlank(groups={"card"})
     * @Assert\Regex(
     *      pattern="/^[0-9]{3,4}$/",
     *      message="api.errors.payment_accounts.card.cvv",
     *      groups={"card"}
     * )
     */
    public $cvv;

    /**
     * Format needs to be yyyyy-mm
     *
     * @Assert\NotBlank(groups={"card"})
     * @Assert\Regex(
     *      pattern="/^[0-9]{4}-[0-9]{2}$/",
     *      message="api.errors.payment_accounts.card.expiration.invalid_format",
     *      groups={"card"}
     * )
     */
    protected $expiration;

    protected $parent;

    public function isValid(ExecutionContextInterface $context)
    {
        if (!$this->isChecksumCorrect()) {
            $context
                ->addViolationAt('account', 'api.errors.payment_accounts.card.account.checksum');
        }

        if (!$this->isExpirationDateValid()) {
            $context
                ->addViolationAt('expiration', 'api.errors.payment_accounts.card.expiration.invalid_expiration');
        }
    }

    /**
     * This is based in Luhn Algorithm
     * @see http://en.wikipedia.org/wiki/Luhn_algorithm
     *
     * @return bool
     */

    public function isChecksumCorrect()
    {
        $cardnumber = $this->account;

        $aux = '';
        foreach (str_split(strrev($cardnumber)) as $pos => $digit) {
            // Multiply * 2 all even digits
            $aux .= ($pos % 2 != 0) ? $digit * 2 : $digit;
        }
        // Sum all digits in string
        $checksum = array_sum(str_split($aux));

        // Card is OK if the sum is an even multiple of 10 and not 0
        return ($checksum != 0 && $checksum % 10 == 0);
    }

    /**
     * @return bool
     */
    public function isExpirationDateValid()
    {
        if (substr($this->expiration, -2) > 12 || $this->expiration < date('Y-m')) {
            return false;
        }

        return true;
    }

    public function getExpiration()
    {
        if (!empty($this->expiration) && is_string($this->expiration)) {
            $expiration = new stdClass();

            $expiration->year = substr($this->expiration, 0, 4);
            $expiration->month = substr($this->expiration, -2);

            $this->expiration = $expiration;
        }

        return $this->expiration;
    }

    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }
}
