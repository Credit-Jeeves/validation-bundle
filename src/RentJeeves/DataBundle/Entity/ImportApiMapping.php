<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportApiMapping as Base;

/**
 * @ORM\Table(
 *      name="rj_import_api_mapping",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_external_property_id",
 *              columns={"holding_id", "external_property_id"}
 *          )
 *      }
 * )
 * @ORM\Entity()
 */
class ImportApiMapping extends Base
{
}
