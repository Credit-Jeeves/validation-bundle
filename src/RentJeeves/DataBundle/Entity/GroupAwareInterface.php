<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;

interface GroupAwareInterface
{
    /**
     * @return Group
     */
    public function getGroup();

    /**
     * @param Group $group
     */
    public function setGroup(Group $group);
}
