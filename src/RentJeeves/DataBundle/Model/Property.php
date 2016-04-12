<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\PropertyAddress as PropertyAddressEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\PropertyMapping as PropertyMappingEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Property
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"RentJeevesImport", "AdminProperty"})
     */
    protected $id;

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
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     mappedBy="property",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $units;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Invite",
     *     mappedBy="property",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $invite;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="group_properties",
     *     cascade={"persist"}
     * )
     */
    protected $property_groups;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     mappedBy="property",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     */
    protected $contracts;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\PropertyMapping",
     *     mappedBy="property",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     * @Serializer\Exclude
     */
    protected $propertyMappings;

    /**
     * @ORM\Column(
     *     name="is_multiple_buildings",
     *     type="boolean"
     * )
     */
    protected $isMultipleBuildings = false;

    /**
     * @var PropertyAddress
     *
     * @ORM\ManyToOne(targetEntity="RentJeeves\DataBundle\Entity\PropertyAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="property_address_id", referencedColumnName="id", nullable=false)
     *
     * @Serializer\SerializedName("propertyAddress")
     * @Serializer\Groups({"payRent"})
     */
    protected $propertyAddress;

    /**
     * @return boolean
     */
    public function isMultipleBuildings()
    {
        return $this->isMultipleBuildings;
    }

    /**
     * @param boolean $isMultipleBuildings
     */
    public function setIsMultipleBuildings($isMultipleBuildings)
    {
        $this->isMultipleBuildings = $isMultipleBuildings;
    }

    public function __construct()
    {
        $this->property_groups = new ArrayCollection();
        $this->units = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->propertyMappings = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param PropertyMappingEntity $propertyMapping
     */
    public function addPropertyMapping(PropertyMappingEntity $propertyMapping)
    {
        $this->propertyMappings[] = $propertyMapping;
    }

    /**
     * @return ArrayCollection|PropertyMappingEntity[]
     */
    public function getPropertyMappings()
    {
        return $this->propertyMappings;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Property
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
     * @return Property
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
     * @param Collection $property_groups
     */
    public function setGroups(Collection $property_groups = null)
    {
        $this->property_groups = $property_groups;
    }

    /**
     * @deprecated use only for for fixture load; if u want update all propertyGroups use `setGroups`
     */
    public function setPropertyGroups($group)
    {
        foreach ($group as $key => $value) {
            $this->addPropertyGroup($value);
            $value->addGroupProperty($this);
        }
    }

    /**
     * Add property_group
     *
     * Does nothing if group is already associated with property
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return Property
     */
    public function addPropertyGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        if (!$this->property_groups->contains($group)) {
            $this->property_groups[] = $group;
        }

        return $this;
    }

    /**
     * Remove property_group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     */
    public function removePropertyGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->property_groups->removeElement($group);
    }

    /**
     * Get property_groups
     *
     * @return \Doctrine\Common\Collections\Collection|\CreditJeeves\DataBundle\Entity\Group[]
     */
    public function getPropertyGroups()
    {
        return $this->property_groups;
    }

    /**
     * Add unit
     *
     * @param \RentJeeves\DataBundle\Entity\Unit
     * @return Property
     */
    public function addUnit(\RentJeeves\DataBundle\Entity\Unit $unit)
    {
        $this->units[] = $unit;

        return $this;
    }

    /**
     * Remove unit
     *
     * @param \RentJeeves\DataBundle\Entity\Unit $unit
     */
    public function removeUnit(\RentJeeves\DataBundle\Entity\Unit $unit)
    {
        $this->units->removeElement($unit);
    }

    /**
     * Get units
     *
     * @return \Doctrine\Common\Collections\Collection|\RentJeeves\DataBundle\Entity\Unit[]
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Add Contract
     *
     * @param Contract $contract
     * @return Property
     */
    public function addContract(Contract $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove Contract
     *
     * @param Contract
     */
    public function removeContract(Contract $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * @return PropertyAddressEntity
     */
    public function getPropertyAddress()
    {
        return $this->propertyAddress;
    }

    /**
     * @param PropertyAddressEntity $propertyAddress
     */
    public function setPropertyAddress(PropertyAddressEntity $propertyAddress)
    {
        $this->propertyAddress = $propertyAddress;
    }
}
