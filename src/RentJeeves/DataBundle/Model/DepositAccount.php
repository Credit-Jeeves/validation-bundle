<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 */
abstract class DepositAccount
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"paymentSelect"});
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="deposit_account"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     * @var \CreditJeeves\DataBundle\Entity\Group
     * @Serializer\Groups({"paymentSelect"});
     * @Serializer\MaxDepth(3)
     */
    protected $group;

    /**
     * @ORM\Column(
     *     name="merchant_name",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $merchantName;

    /**
     * @ORM\Column(
     *     type="DepositAccountStatus",
     *     options={
     *         "default"="init"
     * }
     * )
     *
     */
    protected $status = DepositAccountStatus::DA_INIT;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $message;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="PaymentAccount",
     *      mappedBy="depositAccounts"
     * )
     */
    protected $paymentAccounts;

    public function __construct()
    {
        $this->paymentAccounts = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

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
     * @return CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->group = $group;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add payment account
     *
     * @param \RentJeeves\DataBundle\Entity\PaymentAccount $paymentAccount
     * @return DepositAccount
     */
    public function addPaymentAccount(\RentJeeves\DataBundle\Entity\PaymentAccount $paymentAccount)
    {
        $this->paymentAccounts->add($paymentAccount);
        return $this;
    }

    /**
     * Remove payment account
     *
     * @param \RentJeeves\DataBundle\Entity\PaymentAccount $paymentAccount
     */
    public function removePaymentAccount(\RentJeeves\DataBundle\Entity\PaymentAccount $paymentAccount)
    {
        $this->paymentAccounts->removeElement($paymentAccount);
    }

    /**
     * Get payment accounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPaymentAccounts()
    {
        return $this->paymentAccounts;
    }
}
