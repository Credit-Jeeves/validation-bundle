<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PaymentAccountHpsMerchant as Base;

/**
 * @ORM\Table(
 *     name="rj_hps_payment_account_merchant",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="payment_account_hps_merchant_unique_constraint",
 *              columns={"payment_account_id", "merchant_name"}
 *          )
 *     }
 * )
 * @ORM\Entity()
 */
class PaymentAccountHpsMerchant extends Base
{

}
