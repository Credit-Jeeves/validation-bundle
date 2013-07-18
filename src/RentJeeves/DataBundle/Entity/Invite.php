<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Invite as Base;

/**
 * Invite
 *
 * @ORM\Table(name="rj_invite")
 * @ORM\Entity
 */
class Invite extends Base
{

    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }
}
