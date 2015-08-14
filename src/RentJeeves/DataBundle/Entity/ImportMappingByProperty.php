<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportMappingByProperty as Base;

/**
 * @ORM\Table(
 *      name="rj_import_mapping_by_property"
 * )
 * @ORM\Entity()
 */
class ImportMappingByProperty extends Base
{
}
