<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\DataBundle\Model\PaymentAccount as Base;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="rj_payment_account", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_index", columns={"token"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentAccountRepository")
 * @UniqueEntity("token")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class PaymentAccount extends Base implements UserAwareInterface, PaymentAccountInterface
{

    public function __toString()
    {
        return $this->getName();
    }

    public function setDepositAccounts($depositAccounts)
    {
        foreach ($depositAccounts as $depositAccount) {
            $this->addDepositAccount($depositAccount);
        }
    }
}
