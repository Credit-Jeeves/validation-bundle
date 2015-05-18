<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportError as Base;

/**
 * @ORM\Table(name="rj_import_error")
 * @ORM\Entity()
 */
class ImportError extends Base
{
}
