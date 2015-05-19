<?php

namespace RentJeeves\ApiBundle\Forms\Entity;

use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(groups={"card"}, methods={"validate"})
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
    protected $account;

    /**
     * @Assert\NotBlank(groups={"card"})
     * @Assert\Regex(
     *      pattern="/^[0-9]{3,4}$/",
     *      message="api.errors.payment_accounts.card.cvv",
     *      groups={"card"}
     * )
     */
    protected $cvv;

    /**
     * Format needs to be yyyyy-mm
     *
     * @Assert\NotBlank(groups={"card"})
     */
    protected $expiration;

    /**
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (!$this->isChecksumCorrect()) {
            $context
                ->addViolationAt('account', 'api.errors.payment_accounts.card.account.checksum');
        }

        if (!$this->isDateFormatValid()) {
            $context
                ->addViolationAt('expiration', 'api.errors.payment_accounts.card.expiration.invalid_format');
        } elseif (!$this->isExpirationDateValid()) {
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
        $now = new DateTime();
        $exp = new DateTime($this->expiration);
        $exp->modify($exp->format('Y-m-t'));

        if ($exp < $now) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isDateFormatValid()
    {
        if (preg_match('/^20[0-9]{2}-(0[1-9]|1[0-2])$/', $this->expiration)) {
            return true;
        }

        return false;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function getCvv()
    {
        return $this->cvv;
    }

    public function setCvv($cvv)
    {
        $this->cvv = $cvv;
    }

    /**
     * @return DateTime
     */
    public function getExpiration()
    {
        if (!empty($this->expiration) && is_string($this->expiration)) {
            $this->expiration = new DateTime($this->expiration);
        }

        return $this->expiration;
    }

    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }
}
