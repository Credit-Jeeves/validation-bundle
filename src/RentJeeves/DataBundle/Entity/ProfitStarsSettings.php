<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ProfitStarsSettings as ProfitStarsSettingsModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_profitstars_settings")
 */
class ProfitStarsSettings extends ProfitStarsSettingsModel
{

}
