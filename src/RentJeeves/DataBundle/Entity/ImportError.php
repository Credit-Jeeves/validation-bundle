<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportError as Base;

/**
 * @ORM\Table(
 *     name="rj_import_error",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="unique_exception", columns={"import_summary_id","md5_row_content"}),
 *     }
 * )
 * @ORM\Entity()
 */
class ImportError extends Base
{
}
