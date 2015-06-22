<?php

namespace RentJeeves\ApiBundle\Forms\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Bank
{
    /**
     * @Assert\NotBlank(groups={"bank"})
     */
    protected $routing;

    /**
     * @Assert\NotBlank(groups={"bank"})
     */
    protected $account;

    /**
     * @Assert\NotBlank(groups={"bank"})
     * @Assert\Choice(
     *      message="api.errors.payment_accounts.bank.type",
     *      callback={"RentJeeves\DataBundle\Enum\BankAccountType", "all"},
     *      groups={"bank"}
     * )
     */
    protected $type;

    public function getRouting()
    {
        return $this->routing;
    }

    public function setRouting($routing)
    {
        $this->routing = $routing;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}
