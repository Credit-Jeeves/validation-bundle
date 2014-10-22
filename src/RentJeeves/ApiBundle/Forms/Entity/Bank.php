<?php

namespace RentJeeves\ApiBundle\Forms\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Bank
{
    /**
     * @Assert\NotBlank(groups={"bank"})
     */
    public $routing;

    /**
     * @Assert\NotBlank(groups={"bank"})
     */
    public $account;

    /**
     * @Assert\NotBlank(groups={"bank"})
     * @Assert\Choice(
     *      message="api.errors.payment_accounts.bank.type",
     *      callback={"RentJeeves\ApiBundle\Forms\Enum\BankACHType", "all"},
     *      groups={"bank"}
     * )
     */
    public $type;
}
