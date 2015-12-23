<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportTransformer as Base;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ImportTransformerRepository")
 * @ORM\Table(name="rj_import_transformer")
 */
class ImportTransformer extends Base
{
}
