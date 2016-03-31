<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ProfitStarsTransaction as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_profitstars_transaction")
 */
class ProfitStarsTransaction extends Base
{

}
