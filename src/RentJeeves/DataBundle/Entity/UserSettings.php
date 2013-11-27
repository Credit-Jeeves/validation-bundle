<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\UserSettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="rj_user_settings")
 *
 */
class UserSettings extends Base
{
}
