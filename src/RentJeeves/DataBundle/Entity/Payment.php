<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Payment as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * Payment
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentRepository")
 * @ORM\Table(name="rj_payment")
 */
class Payment extends Base
{
}
