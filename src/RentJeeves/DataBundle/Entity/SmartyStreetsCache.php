<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\SmartyStreetsCache as Base;

/**
 * @ORM\Entity()
 * @ORM\Table(name="rj_smarty_streets_cache")
 */
class SmartyStreetsCache extends Base
{
}
