<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Heartland as Base;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\HeartlandRepository")
 * @ORM\Table(name="rj_checkout_heartland")
 *
 * @method getRequest $this
 */
class Heartland extends Base
{
    public function __toString()
    {
        return (string)$this->getId();
    }
}
