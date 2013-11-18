<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Unit as Base;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Property
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\UnitRepository")
 * @ORM\Table(name="rj_unit")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Unit extends Base
{
    public function __toString()
    {
        return $this->getName();
    }
}
