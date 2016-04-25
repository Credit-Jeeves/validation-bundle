<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ProfitStarsCmid as ProfitStarsCmidModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_profitstars_cmid")
 */
class ProfitStarsCmid extends ProfitStarsCmidModel
{
}
