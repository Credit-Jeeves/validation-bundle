<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\RentManagerSettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_rent_manager_settings")
 */
class RentManagerSettings extends Base
{
}

