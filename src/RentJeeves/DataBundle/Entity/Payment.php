<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Payment as Base;

/**
 * @ORM\Table(name="rj_payment")
 * @ORM\Entity
 */
class Payment extends Base
{
}
