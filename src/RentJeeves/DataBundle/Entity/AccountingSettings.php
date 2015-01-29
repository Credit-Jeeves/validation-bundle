<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\AccountingSettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="accounting_settings")
 */
class AccountingSettings extends Base
{
}
