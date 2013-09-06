<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PaymentAccount as Base;

/**
 * @ORM\Table(name="rj_payment_account")
 * @ORM\Entity
 */
class PaymentAccount extends Base
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Tenant",
     *     inversedBy="payment_accounts"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     * @var \RentJeeves\DataBundle\Entity\Tenant
     */
    protected $user;

    /**
     * Set user
     *
     * @param \RentJeeves\DataBundle\Entity\Tenant $user
     * @return PaymentAccount
     */
    public function setUser(\RentJeeves\DataBundle\Entity\Tenant $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \RentJeeves\DataBundle\Entity\Tenant
     */
    public function getUser()
    {
        return $this->user;
    }
}
