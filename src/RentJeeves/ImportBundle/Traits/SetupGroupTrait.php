<?php

namespace RentJeeves\ImportBundle\Traits;

use CreditJeeves\DataBundle\Entity\Group;

trait SetupGroupTrait
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }
}
