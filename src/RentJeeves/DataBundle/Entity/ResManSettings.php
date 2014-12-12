<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ResManSettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="resman_settings")
 * @ORM\HasLifecycleCallbacks
 */
class ResManSettings extends Base
{

}
