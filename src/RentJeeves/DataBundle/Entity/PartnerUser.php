<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PartnerUser extends User
{
    protected $type = UserType::PARTNER;
}
