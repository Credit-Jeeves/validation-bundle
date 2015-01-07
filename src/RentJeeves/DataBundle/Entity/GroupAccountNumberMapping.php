<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\GroupAccountNumberMapping as Base;

/**
 * @ORM\Table(
 *     name="group_account_mapping",
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="acc_number_constraint",
 *         columns={
 *             "holding_id", "account_number"
 *         }
 *     )
 *     }
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class GroupAccountNumberMapping extends Base
{
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->holding = $this->group->getHolding();
    }
}
