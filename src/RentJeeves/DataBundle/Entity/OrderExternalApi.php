<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\OrderExternalApi as Base;

/**
 * @ORM\Table(name="order_external_api")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\OrderExternalApiRepository")
 */
class OrderExternalApi extends Base
{
}
