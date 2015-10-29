<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class BillingAccountMigration
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="RentJeeves\DataBundle\Entity\BillingAccount")
     * @ORM\JoinColumn(name="heartland_payment_account_id", referencedColumnName="id")
     */
    protected $heartlandBillingAccount;

    /**
     * @ORM\OneToOne(targetEntity="RentJeeves\DataBundle\Entity\BillingAccount")
     * @ORM\JoinColumn(name="aci_payment_account_id", referencedColumnName="id")
     */
    protected $aciBillingAccount;

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
    public function getHeartlandBillingAccount()
    {
        return $this->heartlandBillingAccount;
    }

    /**
     * @param mixed $heartlandBillingAccount
     */
    public function setHeartlandBillingAccount($heartlandBillingAccount)
    {
        $this->heartlandBillingAccount = $heartlandBillingAccount;
    }

    /**
     * @return mixed
     */
    public function getAciBillingAccount()
    {
        return $this->aciBillingAccount;
    }

    /**
     * @param mixed $aciBillingAccount
     */
    public function setAciBillingAccount($aciBillingAccount)
    {
        $this->aciBillingAccount = $aciBillingAccount;
    }
}
