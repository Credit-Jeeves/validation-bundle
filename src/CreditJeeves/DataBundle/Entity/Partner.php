<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Partner as BasePartner;

/**
 * @ORM\Entity
 * @ORM\Table(name="partner")
 */
class Partner extends BasePartner
{
}
