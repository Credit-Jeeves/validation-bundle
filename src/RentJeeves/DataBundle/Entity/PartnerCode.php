<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\PartnerCode as BasePartnerCode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="partner_code")
 */
class PartnerCode extends BasePartnerCode
{
}
