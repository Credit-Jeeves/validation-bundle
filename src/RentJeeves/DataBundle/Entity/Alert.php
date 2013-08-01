<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Alert as Base;

/**
 * Alert
 * @ORM\Entity
 * @ORM\Table(name="rj_alert")
 */
class Alert extends Base
{
}
