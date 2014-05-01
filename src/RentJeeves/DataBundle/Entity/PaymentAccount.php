<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PaymentAccount as Base;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="rj_payment_account")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentAccountRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class PaymentAccount extends Base implements UserAwareInterface
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
