<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PaymentAccount as Base;

/**
 * @ORM\Table(name="rj_payment_account")
 * @ORM\Entity
 */
class PaymentAccount extends Base
{
    /**
     * @var ArrayCollection
     */
    protected $address_choice;

    public function __construct()
    {
        $this->address_choice = new ArrayCollection();
    }

    /**
     * @param ArrayCollection $addressChoice
     * @return PaymentAccount
     */
    public function setAddressChoice($addressChoice)
    {
        $this->address_choice = $addressChoice;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAddressChoice()
    {
        return $this->address_choice;
    }
}
