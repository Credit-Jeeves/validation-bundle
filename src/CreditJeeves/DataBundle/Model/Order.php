<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use \DateTime;
use RentJeeves\DataBundle\Entity\OrderExternalApi;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @ORM\MappedSuperclass
 *
 * Serializer can not work with the existing orderType column.
 * The discriminator field name "orderType" of the base-class "CreditJeeves\DataBundle\Model\\Order" conflicts with
 * a regular property of the sub-class "CreditJeeves\DataBundle\Entity\Order".
 * That is why we use objectType.
 *
 * @Serializer\Discriminator(field = "objectType", map = {
 *    "submerchant": "CreditJeeves\DataBundle\Entity\OrderSubmerchant",
 *    "pay_direct": "CreditJeeves\DataBundle\Entity\OrderPayDirect",
 *    "base": "CreditJeeves\DataBundle\Entity\Order"
 * })
 */
abstract class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(
     *     type="bigint"
     * )
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     */
    protected $id;

    /**
     * @ORM\Column(
     *     type="bigint"
     * )
     */
    protected $cj_applicant_id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     inversedBy="orders"
     * )
     * @ORM\JoinColumn(
     *     name="cj_applicant_id",
     *     referencedColumnName="id"
     * )
     */
    protected $user;

    /**
     * @ORM\Column(
     *     type="OrderStatus",
     *     nullable=false
     * )
     */
    protected $status;

    /**
     * @ORM\Column(
     *     name="payment_type",
     *     type="OrderPaymentType",
     *     nullable=false
     * )
     */
    protected $paymentType;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=false
     * )
     */
    protected $sum;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=true
     * )
     */
    protected $fee = null;

    /**
     * @ORM\Column(
     *     type="datetime"
     * )
     * @Gedmo\Timestampable(on="create")
     */
    protected $created_at;

    /**
     * @ORM\Column(
     *     type="datetime"
     * )
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated_at;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\RentJeeves\DataBundle\Entity\Transaction",
     *     mappedBy="order",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     * @var ArrayCollection
     */
    protected $transactions;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="order",
     *     cascade={"all"}
     * )
     *
     * @var ArrayCollection
     */
    protected $operations;

    /**
     * @ORM\OneToMany(
     *      targetEntity = "\RentJeeves\DataBundle\Entity\JobRelatedOrder",
     *      mappedBy = "order"
     * )
     * @Serializer\Exclude
     */
    protected $jobs;

    /**
     * @ORM\OneToMany(
     *      targetEntity="RentJeeves\DataBundle\Entity\OrderExternalApi",
     *      mappedBy = "order"
     * )
     * @Serializer\Exclude
     */
    protected $sentOrder;

    /**
     * @ORM\Column(
     *     type="PaymentProcessor",
     *     name="payment_processor",
     *     nullable=false
     * )
     */
    protected $paymentProcessor = PaymentProcessor::HEARTLAND;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="descriptor",
     *     nullable=true
     * )
     */
    protected $descriptor;

    /**
     * @var string
     */
    protected $orderType = OrderAlgorithmType::SUBMERCHANT;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->sentOrder = new ArrayCollection();
        $this->created_at = new DateTime();
    }

    /**
     * @param OrderExternalApi $sentOrder
     */
    public function addSendOrder(OrderExternalApi $sentOrder)
    {
        $this->sentOrder->add($sentOrder);
    }

    /**
     * @return ArrayCollection
     */
    public function getSentOrder()
    {
        return $this->sentOrder;
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
     * Set cj_applicant_id
     *
     * @param  integer $cjApplicantId
     * @return self
     */
    public function setCjApplicantId($cjApplicantId)
    {
        $this->cj_applicant_id = $cjApplicantId;

        return $this;
    }

    /**
     * Get cj_applicant_id
     *
     * @return integer
     */
    public function getCjApplicantId()
    {
        return $this->cj_applicant_id;
    }

    /**
     * Set status
     *
     * @param  OrderStatus $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return OrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param  OrderPaymentType $paymentType
     * @return Order
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * Get type
     *
     * @return OrderPaymentType
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Set sum
     *
     * @param  double $sum
     * @return self
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Get fee
     *
     * @return double
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set fee
     *
     * @param  double $fee
     * @return self
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get sum
     *
     * @return double
     */
    public function getSum()
    {
        return (double) $this->sum;
    }

    /**
     * Set created_date
     *
     * @param  DateTime $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_date
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param  DateTime $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set user
     *
     * @param  \CreditJeeves\DataBundle\Entity\User $user
     * @return self
     */
    public function setUser(\CreditJeeves\DataBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add order's operation
     *
     * @param  \CreditJeeves\DataBundle\Entity\Operation $operation
     * @return self
     */
    public function addOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations[] = $operation;

        return $this;
    }

    /**
     * Remove scores
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $operation
     */
    public function removeOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations->removeElement($operation);
    }

    /**
     * Get scores
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Add transaction
     *
     * @param  \RentJeeves\DataBundle\Entity\Transaction $transaction
     * @return self
     */
    public function addTransaction(\RentJeeves\DataBundle\Entity\Transaction $transaction)
    {
        $this->transactions[] = $transaction;

        return $this;
    }

    /**
     * Remove transaction
     *
     * @param \RentJeeves\DataBundle\Entity\Transaction $transaction
     */
    public function removeTransaction(\RentJeeves\DataBundle\Entity\Transaction $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Get Jobs
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJobs()
    {
        return $this->jobs;
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
     * @return string
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @param string $descriptor
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = $descriptor;
    }

    /**
     * @return string
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @param string $orderType
     */
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return 'base';
    }
}
