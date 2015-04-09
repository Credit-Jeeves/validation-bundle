<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Model\DepositAccount as Base;

/**
 * @ORM\Table(name="rj_deposit_account")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\DepositAccountRepository")
 */
class DepositAccount extends Base
{
    public function __construct(Group $group = null)
    {
        if ($group) {
            $this->setGroup($group);
        }

        parent::__construct();
    }

    public function isComplete()
    {
        return $this->status == DepositAccountStatus::DA_COMPLETE && !empty($this->merchantName);
    }

    public function __toString()
    {
        return (string) $this->status;
    }
}
