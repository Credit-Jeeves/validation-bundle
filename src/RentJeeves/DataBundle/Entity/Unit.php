<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Unit as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * Property
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\UnitRepository")
 * @ORM\Table(name="rj_unit")
 */
class Unit extends Base
{
}
