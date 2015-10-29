<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\MerchantAccountMigration as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_merchant_account_migration")
 */
class MerchantAccountMigration extends Base
{

}
