<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Contract
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Tenant",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="tenant_id",
     *     referencedColumnName="id"
     * )
     */
    protected $tenant;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id"
     * )
     */
    protected $holding;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="property_id",
     *     referencedColumnName="id"
     * )
     * @Assert\NotBlank(
     *     message="error.property.empty",
     *     groups={
     *         "tenant_invite"
     *     }
     * )
     */
    protected $property;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="unit_id",
     *     referencedColumnName="id"
     * )
     * @Assert\NotBlank(
     *     message="error.unit.empty",
     *     groups={
     *         "tenant_invite"
     *     }
     * )
     */
    protected $unit;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @Assert\Length(
     *     max=15,
     *     maxMessage="unit.name.long",
     *     groups={
     *         "tenant_contract",
     *     }
     * )
     */
    protected $search;

    /**
     * @ORM\Column(
     *     type="ContractStatus",
     *     options={
     *         "default"="pending"
     *     }
     * )
     */
    protected $status;

    /**
     * @ORM\Column(
     *     type="integer",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.rent.empty",
     *     groups={
     *         "tenant_invite"
     *     }
     * )
     */
    protected $rent;

    /**
     * @ORM\Column(
     *     type="integer"
     * )
     */
    protected $due_day = 1;

    /**
     * @ORM\Column(
     *     type="datetime",
     *     nullable=true
     * )
     */
    protected $paid_to;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     nullable=true,
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $reporting = 0;

    /**
     * @ORM\Column(
     *     name="start_at",
     *     type="datetime",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.unit.empty",
     *     groups={
     *         "tenant_invite"
     *     }
     * )
     */
    protected $startAt;

    /**
     * @ORM\Column(
     *     name="finish_at",
     *     type="datetime",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.unit.empty",
     *     groups={
     *         "tenant_invite"
     *     }
     * )
     */
    protected $finishAt;
    

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="contract",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $operations;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
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
     * Set Tenant
     *
     * @param \CreditJeeves\DataBundle\Entity\Tenant $tenant
     * @return contract
     */
    public function setTenant(\CreditJeeves\DataBundle\Entity\Tenant $tenant)
    {
        $this->tenant = $tenant;
        return $this;
    }

    /**
     * Get Tenant
     *
     * @return \CreditJeeves\DataBundle\Entity\Tenant
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Set Holding
     *
     * @param Holding $holding
     * @return Contract
     */
    public function setHolding(\CreditJeeves\DataBundle\Entity\Holding $holding)
    {
        $this->holding = $holding;
        return $this;
    }

    /**
     * Get Holding
     *
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * Set Group
     *
     * @param Holding $holding
     * @return Contract
     */
    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get Group
     *
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set Property
     *
     * @param Property $property
     * @return Contract
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Get Property
     *
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set Unit
     *
     * @param Unit $unit
     * @return Contract
     */
    public function setUnit(Unit $unit = null)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * Get Unit
     *
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set search
     *
     * @param string $search
     * @return Contract
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Get search
     *
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Unit
     */
    public function setStatus($status = ContractStatus::PENDING)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set rent
     *
     * @param double $rent
     * @return Unit
     */
    public function setRent($rent)
    {
        $this->rent = $rent;
        return $this;
    }

    /**
     * Get rent
     *
     * @return double
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * Set Due day
     *
     * @param integer $dueDay
     * @return Unit
     */
    public function setDueDay($dueDay)
    {
        $this->due_day = $dueDay;
        return $this;
    }

    /**
     * Get Due Day
     *
     * @return integer
     */
    public function getDueDay()
    {
        return $this->due_day;
    }

    /**
     * Set Paid to
     *
     * @param \DateTime $paidTo
     * @return Contract
     */
    public function setPaidTo($paidTo)
    {
        $this->paid_to = $paidTo;
        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getPaidTo()
    {
        return $this->paid_to;
    }

    /**
     * Set Reporting
     *
     * @param boolean $reporting
     * @return Contract
     */
    public function setReporting($reporting)
    {
        $this->reporting = $reporting;
        return $this;
    }

    /**
     * Get Reporting
     *
     * @return boolean
     */
    public function getReporting()
    {
        return $this->reporting;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     * @return Contract
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set finishAt
     *
     * @param \DateTime $finishAt
     * @return Contract
     */
    public function setFinishAt($finishAt)
    {
        $this->finishAt = $finishAt;
        return $this;
    }

    /**
     * Get finishAt
     *
     * @return \DateTime
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Contract
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Contract
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add operation
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $operation
     * @return Contract
     */
    public function addOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations[] = $operation;
        return $this;
    }

    /**
     * Remove operation
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $opeartion
     */
    public function removeScore(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->opeartions->removeElement($operation);
    }

    /**
     * Get operations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperations()
    {
        return $this->operations;
    }
}
