<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\DepositAccount as Base;

/**
 * @ORM\Table(name="rj_deposit_account")
 * @ORM\Entity
 */
class DepositAccount extends Base
{
}
