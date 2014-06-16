<?php

namespace RentJeeves\DataBundle\Entity;

interface GroupAwareInterface
{
    public function getGroup();
    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group);
}
