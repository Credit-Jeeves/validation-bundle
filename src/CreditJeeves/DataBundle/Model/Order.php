<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use \DateTime;
use RentJeeves\DataBundle\Entity\OrderExternalApi;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @ORM\MappedSuperclass
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
     *     options={
     *         "default"="new"
     *     }
     * )
     */
    protected $status = OrderStatus::NEWONE;

    /**
     * @ORM\Column(
     *     type="OrderType",
     *     nullable=true
     * )
     */
    protected $type = OrderType::CASH;

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

    public function __construct()
    {
        $this->operations   = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->operations   = new ArrayCollection();
        $this->sentOrder    = new ArrayCollection();
        $this->created_at   = new DateTime();
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
     * @return Order
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
     * @return Order
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
     * @param  OrderType $type
     * @return Order
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return OrderType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sum
     *
     * @param  double $sum
     * @return Order
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
     * @return Order
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
     * @return Order
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
     * @return Order
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
     * @return Order
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
     * @return Order
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
     * @return Order
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
}
