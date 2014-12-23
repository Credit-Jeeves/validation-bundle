<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\UnitMapping as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\UnitMappingRepository")
 * @ORM\Table(name="rj_unit_mapping")
 */
class UnitMapping extends Base
{

}
