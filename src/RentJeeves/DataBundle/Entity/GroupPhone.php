<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\GroupPhone as Base;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Property
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\GroupPhoneRepository")
 * @ORM\Table(name="rj_group_phone")
 */
class GroupPhone extends Base
{
    public function __toString()
    {
        return $this->getName();
    }
}
