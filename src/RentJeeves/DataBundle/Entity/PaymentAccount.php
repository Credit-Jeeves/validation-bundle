<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\DataBundle\Model\PaymentAccount as Base;

/**
 * @ORM\Table(name="rj_payment_account")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentAccountRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class PaymentAccount extends Base implements UserAwareInterface, PaymentAccountInterface
{

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param string $merchantName
     * @return bool
     */
    public function hasAssociatedHpsMerchant($merchantName)
    {
        if (null !== $this->getAssociatedHpsMerchant($merchantName)) {
            return true;
        }

        return false;
    }

    /**
     * @param $merchantName
     * @return null|PaymentAccountHpsMerchant
     */
    public function getAssociatedHpsMerchant($merchantName)
    {
        /** @var PaymentAccountHpsMerchant $merchant */
        foreach ($this->getHpsMerchants() as $merchant) {
            if ($merchantName == $merchant->getMerchantName()) {
                return $merchant;
            }
        }

        return null;
    }
}
