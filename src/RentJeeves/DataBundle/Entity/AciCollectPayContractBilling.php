<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\AciCollectPayContractBilling as Base;

/**
 * @ORM\Table(
 *      name="rj_aci_collect_pay_contract_billing",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="billing_account_unique_constraint",
 *              columns={"contract_id", "division_id"}
 *          )
 *     }
 * )
 * @ORM\Entity
 */
class AciCollectPayContractBilling extends Base
{
}
