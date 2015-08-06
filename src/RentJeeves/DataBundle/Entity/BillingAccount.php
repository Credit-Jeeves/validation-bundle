<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\DataBundle\Model\BillingAccount as Base;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="rj_billing_account", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_index", columns={"token"})
 *  }
 * ))
 * @UniqueEntity("token")
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
