<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Checkout as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\CheckoutStatus;

/**
 * Contract
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\CheckoutRepository")
 * @ORM\Table(name="rj_checkout_heartland")
 */
class Checkout extends Base
{
}
