<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\PaymentAccount as PaymentAccountEntity;

/**
 * @ORM\MappedSuperclass
 */
class PaymentAccountHpsMerchant
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="merchant_name",
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     *
     * @var string
     */
    protected $merchantName;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount",
     *     inversedBy="hpsMerchants",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="payment_account_id", referencedColumnName="id")
     *
     * @var PaymentAccountEntity
     */
    protected $paymentAccount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * @param string $merchantName
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
    }

    /**
     * @return PaymentAccountEntity
     */
    public function getPaymentAccount()
    {
        return $this->paymentAccount;
    }

    /**
     * @param PaymentAccountEntity $paymentAccount
     */
    public function setPaymentAccount($paymentAccount)
    {
        $this->paymentAccount = $paymentAccount;
    }
}
