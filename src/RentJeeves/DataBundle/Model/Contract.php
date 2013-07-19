<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

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
     */
    protected $unit;

    /**
     * @ORM\Column(
     *     type="string"
     * )
     * @Assert\NotBlank(
     *     message="unit.empty",
     *     groups={
     *         "tenant_contract",
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=15,
     *     minMessage="unit.name.short",
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
     */
    protected $rent;

    /**
     * @ORM\Column(
     *     name="start_at",
     *     type="datetime"
     * )
     */
    protected $startAt;

    /**
     * @ORM\Column(
     *     name="finish_at",
     *     type="datetime"
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
}
