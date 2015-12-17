<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Import as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_import")
 */
class Import extends Base
{
    public function getCountImportProperties()
    {
        return $this->getImportProperties()->count();
    }
}
