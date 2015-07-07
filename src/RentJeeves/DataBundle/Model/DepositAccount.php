<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\ManyToMany(
     *      targetEntity="PaymentAccount",
     *      mappedBy="depositAccounts",
     *      cascade={"remove"}
     * )
     * @Serializer\SerializedName("paymentAccounts")
     */
    protected $paymentAccounts;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $mid;

    /**
     * @ORM\Column(
     *     type="PaymentProcessor",
     *     name="payment_processor",
     *     nullable=false
     * )
     */
    protected $paymentProcessor = PaymentProcessor::HEARTLAND;

    public function __construct()
    {
        $this->paymentAccounts = new ArrayCollection();
        $this->passedAch = false;
    }

    /**
     * @return string
     */
    public function getPaymentProcessor()
    {
        return $this->paymentProcessor;
    }

    /**
     * @param string $paymentProcessor
     */
    public function setPaymentProcessor($paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
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
     * @param  string         $merchantName
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
     * Add payment account
     *
     * @param  \RentJeeves\DataBundle\Entity\PaymentAccount $paymentAccount
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

    /**
     * @return string
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * @param string $mid
     */
    public function setMid($mid)
    {
        $this->mid = $mid;
    }
}
