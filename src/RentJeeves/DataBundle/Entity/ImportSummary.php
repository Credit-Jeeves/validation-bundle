<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportSummary as Base;

/**
 * @ORM\Table(name="rj_import_summary")
 * @ORM\Entity()
 */
class ImportSummary extends Base
{
}
