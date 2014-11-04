<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PartnerUserMapping as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="partner_user")
 */
class PartnerUserMapping extends Base
{

}
