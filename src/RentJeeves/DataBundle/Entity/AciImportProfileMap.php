<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\AciImportProfileMap as Base;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\AciImportProfileMapRepository")
 * @ORM\Table(name="rj_aci_import_profile_map")
 */
class AciImportProfileMap extends Base
{

}
