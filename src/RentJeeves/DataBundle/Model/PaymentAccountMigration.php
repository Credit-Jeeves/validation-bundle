<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class PaymentAccountMigration
{
    /**
     * @ORM\Column()
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount")
     * @ORM\JoinColumn(name="heartland_payment_account_id", referencedColumnName="id")
     */
    protected $heartlandPaymentAccount;

    /**
     * @ORM\OneToOne(targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount")
     * @ORM\JoinColumn(name="aci_payment_account_id", referencedColumnName="id")
     */
    protected $aciPaymentAccount;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getHeartlandPaymentAccount()
    {
        return $this->heartlandPaymentAccount;
    }

    /**
     * @param mixed $heartlandPaymentAccount
     */
    public function setHeartlandPaymentAccount($heartlandPaymentAccount)
    {
        $this->heartlandPaymentAccount = $heartlandPaymentAccount;
    }

    /**
     * @return mixed
     */
    public function getAciPaymentAccount()
    {
        return $this->aciPaymentAccount;
    }

    /**
     * @param mixed $aciPaymentAccount
     */
    public function setAciPaymentAccount($aciPaymentAccount)
    {
        $this->aciPaymentAccount = $aciPaymentAccount;
    }
}
