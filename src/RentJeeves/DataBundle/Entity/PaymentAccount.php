<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PaymentAccount as Base;

/**
 * @ORM\Table(name="rj_payment_account")
 * @ORM\Entity
 */
class PaymentAccount extends Base
{
}
