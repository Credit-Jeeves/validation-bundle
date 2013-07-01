<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $cj_applicant_id;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="OrderStatus", options={"default"="new"})
     */
    protected $status = OrderStatus::NEWONE;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @var CheckoutAuthorizeNetAim
     *
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim",
     *     mappedBy="order",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     *
     * @ORM\JoinColumn(name="id", referencedColumnName="cj_order_id")
     */
    protected $authorize;

//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(
//     *     targetEntity="OrderOperation",
//     *     mappedBy="order",
//     *     cascade={"persist", "remove", "merge"},
//     *     orphanRemoval=true
//     * )
//     */
//    protected $orderOperations;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\CreditJeeves\DataBundle\Entity\Operation", inversedBy="orders")
     * @ORM\JoinTable(
     *      name="cj_order_operation",
     *      joinColumns={@ORM\JoinColumn(name="cj_order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="cj_operation_id", referencedColumnName="id")}
     * )
     */
    protected $operations;

    public function __construct()
    {
//        $this->orderOperations = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->created_at = new \DateTime();
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
     * Set created_date
     *
     * @param \DateTime $createdDate
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
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
     * @return \DateTime
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
     * @param \CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim $authorize
     */
    public function setAuthorize(\CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim $authorize = null)
    {
        $this->authorize = $authorize;
    }

    public function getAuthorize()
    {
        return $this->authorize;
    }

    /**
     * Add order's operation
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $orderOperations
     * @return User
     */
    public function addOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations[] = $operation;

        return $this;
    }

    /**
     * Remove scores
     *
     * @param \CreditJeeves\DataBundle\Entity\OrderOperation $operation
     */
    public function removeOperation(\CreditJeeves\DataBundle\Entity\OrderOperation $operation)
    {
        $this->orderOperations->removeElement($operation);
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
}
