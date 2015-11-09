<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportGroupSettings as Base;

/**
 * @ORM\Table(
 *      name="rj_import_group_settings"
 * )
 * @ORM\Entity()
 */
class ImportGroupSettings extends Base
{
}
