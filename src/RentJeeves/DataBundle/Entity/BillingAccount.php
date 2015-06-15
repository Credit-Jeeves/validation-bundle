<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\DataBundle\Model\BillingAccount as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="rj_billing_account")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\BillingAccountRepository")
 */
class BillingAccount extends Base implements GroupAwareInterface, PaymentAccountInterface
{
    public function getType()
    {
        return PaymentAccountTypeEnum::BANK;
    }
}
