<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\OrderExternalApi as Base;

/**
 * @ORM\Entity()
 * @ORM\Table(name="order_external_api")
 */
class OrderExternalApi extends Base
{
}
