<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PaymentAccountMigration as baseBillingAccountMigration;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="rj_payment_account_migration")
 * @ORM\Entity()
 * @UniqueEntity(fields={"heartlandBillingAccount", "aciBillingAccount"})
 */
class PaymentAccountMigration extends baseBillingAccountMigration
{

}
