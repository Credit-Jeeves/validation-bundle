<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\DataBundle\Model\BillingAccount as Base;

/**
 * @ORM\Table(name="rj_billing_account")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\BillingAccountRepository")
 */
class BillingAccount extends Base implements GroupAwareInterface, PaymentAccountInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->getNickname();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return PaymentAccountTypeEnum::BANK;
    }
}
