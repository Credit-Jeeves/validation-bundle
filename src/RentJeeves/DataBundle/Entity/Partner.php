<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Partner as BasePartner;

/**
 * @ORM\Entity
 * @ORM\Table(name="partner")
 */
class Partner extends BasePartner
{
}
