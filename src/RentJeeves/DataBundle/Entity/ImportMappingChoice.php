<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportMappingChoice as Base;

/**
 * @ORM\Table(
 *     name="import_mapping",
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="unique_index_constraint",
 *         columns={
 *             "group_id", "header_hash"
 *         }
 *     )
 *     }
 * )
 * @ORM\Entity()
 */
class ImportMappingChoice extends Base
{

}
