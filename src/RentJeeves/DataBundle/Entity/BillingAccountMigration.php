<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\BillingAccountMigration as baseBillingAccountMigration;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="rj_billing_account_migration")
 * @ORM\Entity()
 * @UniqueEntity(fields={"heartlandBillingAccount", "aciBillingAccount"})
 */
class BillingAccountMigration extends baseBillingAccountMigration
{
}
