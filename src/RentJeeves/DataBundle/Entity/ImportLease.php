<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ImportLease as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_import_lease")
 */
class ImportLease extends Base
{

}
