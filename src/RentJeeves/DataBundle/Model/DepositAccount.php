<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class DepositAccount extends PaymentAccount
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Landlord",
     *     inversedBy="payment_accounts"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     * @var \RentJeeves\DataBundle\Entity\Landlord
     */
    protected $user;

    /**
     * @ORM\Column(
     *     name="merchant_name",
     *     type="string",
     *     length=255
     * )
     */
    protected $merchantName;

    /**
     * Set processorToken
     *
     * @param string $merchantName
     * @return DepositAccount
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;

        return $this;
    }

    /**
     * Get processorToken
     *
     * @return string
     */
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Landlord
     */
    public function getUser()
    {
        return parent::getUser();
    }
}
