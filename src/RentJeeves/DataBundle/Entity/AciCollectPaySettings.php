<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\AciCollectPaySettings as Base;

/**
 * @ORM\Table(name="rj_aci_collect_pay_settings")
 * @ORM\Entity
 */
class AciCollectPaySettings extends Base
{
}
