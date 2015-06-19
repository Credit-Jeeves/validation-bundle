<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\BaseOrder as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="orderType", type="OrderAlgorithmType")
 * @ORM\DiscriminatorMap({
 *      "submerchant" = "CreditJeeves\DataBundle\Entity\Order",
 *      "pay_direct" = "CreditJeeves\DataBundle\Entity\OrderPayDirect"
 * })
 * @ORM\Table(name="cj_order")
 */
abstract class BaseOrder extends Base
{

}
