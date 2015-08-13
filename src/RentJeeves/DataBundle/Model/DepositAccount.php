<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\Payment as PaymentEntity;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

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
     * @ORM\ManyToOne(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *      inversedBy="depositAccounts"
     * )
     * @ORM\JoinColumn(
     *      name="group_id",
     *      referencedColumnName="id",
     *      nullable=false
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

    /**
     * @var string
     *
     * @ORM\Column(type="DepositAccountType", options={"default" = "rent"})
     */
    protected $type = DepositAccountType::RENT;

    /**
     * @ORM\OneToMany(
     *      targetEntity="RentJeeves\DataBundle\Entity\Payment",
     *      mappedBy="depositAccount",
     *      cascade={"persist"}
     * )
     * @var ArrayCollection
     */
    protected $payments;

    /**
     * @ORM\OneToMany(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Order",
     *      mappedBy="depositAccount",
     *      cascade={"persist"}
     * )
     * @var ArrayCollection
     */
    protected $orders;

    public function __construct()
    {
        $this->paymentAccounts = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->orders = new ArrayCollection();
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

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /*
     * @return ArrayCollection|PaymentEntity[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @return ArrayCollection|Order[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param PaymentEntity $payment
     */
    public function addPayment(PaymentEntity $payment)
    {
        $this->payments->add($payment);
    }

    /**
     * @param Order $order
     */
    public function addOrder(Order $order)
    {
        $this->orders->add($order);
    }
}
