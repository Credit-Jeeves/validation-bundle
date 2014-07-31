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
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *      inversedBy="depositAccount"
     * )
     * @ORM\JoinColumn(
     *      name="group_id",
     *      referencedColumnName="id"
     * )
     * @var \CreditJeeves\DataBundle\Entity\Group
     */
    protected $group;

    /**
     * @ORM\Column(
     *      name="merchant_name",
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     * @Serializer\SerializedName("merchantName")
     */
    protected $merchantName;

    /**
     * @ORM\Column(
     *      name="account_number",
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     * @Serializer\SerializedName("accountNumber")
     */
    protected $accountNumber;

    /**
     * @ORM\Column(
     *      type="DepositAccountStatus",
     *      options={
     *         "default"="init"
     *      }
     * )
     *
     */
    protected $status = DepositAccountStatus::DA_INIT;

    /**
     * @ORM\Column(
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     */
    protected $message;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=true
     * )
     * @Serializer\SerializedName("feeCC")
     * @Serializer\Groups({"payRent"})
     */
    protected $feeCC;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=true
     * )
     * @Serializer\SerializedName("feeACH")
     * @Serializer\Groups({"payRent"})
     */
    protected $feeACH;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="PaymentAccount",
     *      mappedBy="depositAccounts"
     * )
     * @Serializer\SerializedName("paymentAccounts")
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
     * @param string $accountNumber
     * @return DepositAccount
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
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
     * @param float $feeACH
     */
    public function setFeeACH($feeACH)
    {
        $this->feeACH = $feeACH;
    }

    /**
     * @return float
     */
    public function getFeeACH()
    {
        return $this->feeACH;
    }

    /**
     * @param float $feeCC
     */
    public function setFeeCC($feeCC)
    {
        $this->feeCC = $feeCC;
    }

    /**
     * @return float
     */
    public function getFeeCC()
    {
        return $this->feeCC;
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
