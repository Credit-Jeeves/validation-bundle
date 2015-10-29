<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\PropertyAddress as PropertyAddressEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

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
     * @ORM\Column(
     *     name="country",
     *     type="string",
     *     length=3
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $country;

    /**
     * @ORM\Column(
     *     name="area",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $area;

    /**
     * @ORM\Column(
     *     name="city",
     *     type="string",
     *     length=255
     * )
     * @Assert\NotBlank()
     * @Serializer\Groups({"payRent"})
     */
    protected $city;

    /**
     * @ORM\Column(
     *     name="district",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $district;

    /**
     * @ORM\Column(
     *     name="street",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $street;

    /**
     * @ORM\Column(
     *     name="number",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     * @Assert\NotBlank()
     * @Serializer\Groups({"payRent"})
     */
    protected $number;

    /**
     * @ORM\Column(
     *     name="zip",
     *     type="string",
     *     length=15,
     *     nullable=true
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $zip;

    /**
     * @ORM\Column(
     *     name="google_reference",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $google_reference;

    /**
     * @ORM\Column(
     *     name="jb",
     *     type="float",
     *     nullable=true
     * )
     */
    protected $jb;

    /**
     * @ORM\Column(
     *     name="kb",
     *     type="float",
     *     nullable=true
     * )
     */
    protected $kb;

    /**
     * @ORM\Column(
     *     name="is_single",
     *     type="boolean",
     *     nullable=true
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $isSingle;

    /**
     * @ORM\Column(
     *     name="ss_lat",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $lat;

    /**
     * @ORM\Column(
     *     name="ss_long",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $long;

    /**
     * @ORM\Column(
     *
     *     name="ss_index",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $index;

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
    protected $propertyMapping;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ContractWaiting",
     *     mappedBy="property",
     *     cascade={
     *       "persist"
     *     }
     * )
     * @Serializer\Exclude
     */
    protected $contractsWaiting;

    /**
     * @ORM\Column(
     *     name="is_multiple_buildings",
     *     type="boolean"
     * )
     */
    protected $isMultipleBuildings = false;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\ImportMappingByProperty",
     *     mappedBy="property"
     * )
     * @Serializer\Exclude
     *
     * @var ImportMappingByProperty
     */
    protected $importMappingByProperty;

    /**
     * @var PropertyAddress
     *
     * @ORM\ManyToOne(targetEntity="RentJeeves\DataBundle\Entity\PropertyAddress")
     * @ORM\JoinColumn(name="property_address_id", referencedColumnName="id", nullable=true)
     * @TODO: ORM\JoinColumn(name="property_address_id", referencedColumnName="id") after migration
     */
    protected $propertyAddress;

    /**
     * @return ImportMappingByProperty
     */
    public function getImportMappingByProperty()
    {
        return $this->importMappingByProperty;
    }

    /**
     * @param ImportMappingByProperty $importMappingByProperty
     */
    public function setImportMappingByProperty(ImportMappingByProperty $importMappingByProperty)
    {
        $this->importMappingByProperty = $importMappingByProperty;
    }

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
        $this->contractsWaiting = new ArrayCollection();
    }

    /**
     * @param ContractWaiting $contractsWaiting
     */
    public function addContractsWaiting(ContractWaiting $contractsWaiting)
    {
        $this->contractsWaiting = $contractsWaiting;
    }

    /**
     * @return ContractWaiting
     */
    public function getContractsWaiting()
    {
        return $this->contractsWaiting;
    }

    /**
     * @param PropertyMapping $propertyMapping
     */
    public function setPropertyMapping(PropertyMapping $propertyMapping)
    {
        $this->propertyMapping = $propertyMapping;
    }

    /**
     * @return PropertyMapping
     */
    public function getPropertyMapping()
    {
        return $this->propertyMapping;
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
     * Set country
     *
     * @param string $country
     * @return Property
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return Property
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Property
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set district
     *
     * @param string $district
     * @return Property
     */
    public function setDistrict($district)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set street_address
     *
     * @param string $streetAddress
     * @return Property
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street_address
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street_number
     *
     * @param string $streetNumber
     * @return Property
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get street_number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Property
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set jb
     *
     * @param float $jb
     * @return Property
     */
    public function setJb($jb)
    {
        $this->jb = $jb;

        return $this;
    }

    /**
     * Get jb
     *
     * @return float
     */
    public function getJb()
    {
        return $this->jb;
    }

    /**
     * Set kb
     *
     * @param float $kb
     * @return Property
     */
    public function setKb($kb)
    {
        $this->kb = $kb;

        return $this;
    }

    /**
     * Get jb
     *
     * @return float
     */
    public function getKb()
    {
        return $this->kb;
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
     * You should set this using "property.manager"->setupSingleProperty() instead.
     *
     * @param boolean $isSingle
     *
     * @deprecated
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = $isSingle;
    }

    /**
     * @deprecated Please use function on the following line for getting value
     * @see RentJeeves\DataBundle\Entity\Property::isSingle()
     *
     * @return boolean|null
     */
    public function getIsSingle()
    {
        return $this->isSingle;
    }

    /**
     * Add groups
     * This is used for fixture load
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $groups
     * @return Property
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
     * @return \Doctrine\Common\Collections\Collection
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
     * @return \Doctrine\Common\Collections\Collection
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

    public function setGoogleReference($google_reference)
    {
        $this->google_reference = $google_reference;

        return $this;
    }

    public function getGoogleReference()
    {
        return $this->google_reference;
    }

    /**
     * @deprecated use setJb
     */
    public function setLatitude($data)
    {
        return $this->setJb($data);
    }

    /**
     * @deprecated use setKb
     */
    public function setLongitude($data)
    {
        return $this->setKb($data);
    }

    /**
     * @deprecated use getJb
     */
    public function getLatitude()
    {
        return $this->getJb();
    }

    /**
     * @deprecated use getKb
     */
    public function getLongitude()
    {
        return $this->getKb();
    }

    /**
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param string $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return string
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @param string $long
     */
    public function setLong($long)
    {
        $this->long = $long;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param string $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return sprintf('%s %s', $this->number, $this->street);
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
