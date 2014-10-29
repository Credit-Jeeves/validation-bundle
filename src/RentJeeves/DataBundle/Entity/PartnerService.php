<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PartnerService as BasePartner;

/**
 * @ORM\Entity
 * @ORM\Table(name="partner")
 */
class PartnerService extends BasePartner
{
}
