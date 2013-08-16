<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Heartland as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\HeartlandStatus;

/**
 * Contract
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\HeartlandRepository")
 * @ORM\Table(name="rj_checkout_heartland")
 */
class Heartland extends Base
{
}
