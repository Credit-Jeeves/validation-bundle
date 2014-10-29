<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PartnerUser as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="partner_user")
 */
class PartnerUser extends Base
{

}
