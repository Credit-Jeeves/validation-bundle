<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\MRISettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_mri_settings")
 */
class MRISettings extends Base
{
}
