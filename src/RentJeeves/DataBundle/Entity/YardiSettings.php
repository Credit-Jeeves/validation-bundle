<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\YardiSettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="yardi_settings")
 * @ORM\HasLifecycleCallbacks
 */
class YardiSettings extends Base
{
}
