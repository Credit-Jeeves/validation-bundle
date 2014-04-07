<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use \DateTime;

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
    protected $type = OrderType::AUTHORIZE_CARD;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=false
     * )
     */
    protected $amount;

    /**
     * @ORM\Column(
     *     type="integer",
     *     nullable=true
     * )
     */
    protected $days_late = null;

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
     *     targetEntity="CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim",
     *     mappedBy="order",
     *     cascade={"persist", "remove", "merge"}
     * )
     *
     * @var ArrayCollection
     */
    protected $authorizes;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\RentJeeves\DataBundle\Entity\Heartland",
     *     mappedBy="order",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     * @var ArrayCollection
     */
    protected $heartlands;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="order",
     *     cascade={"all"}
     * )
     *
     * @Serializer\SerializedName("Details")
     * @Serializer\XmlList(inline = false, entry="Detail")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"xmlReport"})
     *
     * @var ArrayCollection
     */
    protected $operations;

    /**
     * @ORM\OneToMany(targetEntity = "\RentJeeves\DataBundle\Entity\JobRelatedOrder", mappedBy = "order")
     * @Serializer\Exclude
     */
    protected $jobs;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->authorizes = new ArrayCollection();
        $this->heartlands = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->created_at = new DateTime();
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
     * @param integer $cjApplicantId
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
     * @param OrderStatus $status
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
     * @param OrderType $type
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
     * Set amount
     *
     * @param double $amount
     * @return Order
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }
    
    /**
     * Get amount
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set days_late
     *
     * @param double $days
     * @return Order
     */
    public function setDaysLate($days)
    {
        $this->days_late = $days;
    
        return $this;
    }
    
    /**
     * Get days_late
     *
     * @return double
     */
    public function getDaysLate()
    {
        return $this->days_late;
    }
    
    
    /**
     * Set created_date
     *
     * @param DateTime $createdDate
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
     * @param DateTime $updatedAt
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
     * @param \CreditJeeves\DataBundle\Entity\User $user
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
     * Add Authorize
     *
     * @param \CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim
     * @return Order
     */
    public function addAuthorize(\CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim $authorize)
    {
        $this->authorizes[] = $authorize;

        return $this;
    }

    /**
     * Set authorizes
     *
     * @param ArrayCollection $authorizes
     *
     * @return Order
     */
    public function setAuthorizes(ArrayCollection $authorizes)
    {
        $this->authorizes = $authorizes;

        return $this;
    }

    /**
     * Remove authorize
     *
     * @param \CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim $authorize
     */
    public function removeAuthorize(\CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim $authorize)
    {
        $this->authorizes->removeElement($authorize);
    }

    /**
     * Get authorizes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthorizes()
    {
        return $this->authorizes;
    }

    /**
     * Add order's operation
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $operations
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
     * Add heartland 
     *
     * @param \RentJeeves\DataBundle\Entity\Heartland
     * @return Order
     */
    public function addHeartland(\RentJeeves\DataBundle\Entity\Heartland $heartland)
    {
        $this->heartlands[] = $heartland;
    
        return $this;
    }

    /**
     * Remove heartland
     *
     * @param \RentJeeves\DataBundle\Entity\Heartland $heartland
     */
    public function removeHeartland(\RentJeeves\DataBundle\Entity\Heartland $heartland)
    {
        $this->heartlands->removeElement($heartland);
    }

    /**
     * Get heartlands
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHeartlands()
    {
        return $this->heartlands;
    }
}
