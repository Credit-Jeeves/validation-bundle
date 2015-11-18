<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\PropertyAddress as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_property_address")
 */
class PropertyAddress extends Base
{
}
