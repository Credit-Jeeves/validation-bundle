<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\GroupSettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * GroupSettings
 *
 * @ORM\Entity()
 * @ORM\Table(name="rj_group_settings")
 */
class GroupSettings extends Base
{
}
