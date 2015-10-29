<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Model\DepositAccount as Base;

/**
 * @ORM\Table(
 *      name="rj_deposit_account",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="da_unique_constraint",
 *              columns={"type", "group_id", "payment_processor"}
 *          ),
 *          @ORM\UniqueConstraint(
 *              name="unique_constraint_account_number",
 *              columns={"type", "holding_id", "account_number", "payment_processor"}
 *          )
 *     }
 * )
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\DepositAccountRepository")
 */
class DepositAccount extends Base
{
    public function __construct(Group $group = null)
    {
        if ($group) {
            $this->setGroup($group);
            $this->setHolding($group->getHolding());
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
