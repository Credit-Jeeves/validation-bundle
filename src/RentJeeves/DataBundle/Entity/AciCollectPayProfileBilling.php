<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\AciCollectPayProfileBilling as Base;

/**
 * @ORM\Table(
 *      name="rj_aci_collect_pay_profile_billing",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="profile_billing_unique_constraint",
 *              columns={"profile_id", "division_id"}
 *          )
 *     }
 * )
 * @ORM\Entity
 */
class AciCollectPayProfileBilling extends Base
{

}
