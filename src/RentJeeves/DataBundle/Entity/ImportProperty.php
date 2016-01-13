<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportProperty as Base;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ImportPropertyRepository")
 * @ORM\Table(name="rj_import_property")
 */
class ImportProperty extends Base
{

}
