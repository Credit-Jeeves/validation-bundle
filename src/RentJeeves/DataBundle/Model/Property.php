<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
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
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="country",
     *     type="string",
     *     length=3
     * )
     */
    protected $country;

    /**
     * @ORM\Column(
     *     name="area",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $area;

    /**
     * @ORM\Column(
     *     name="city",
     *     type="string",
     *     length=255
     * )
     * @Assert\NotBlank()
     */
    protected $city;

    /**
     * @ORM\Column(
     *     name="district",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $district;

    
    /**
     * @ORM\Column(
     *     name="street",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
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
     */
    protected $number;

    /**
     * @ORM\Column(
     *     name="zip",
     *     type="string",
     *     length=15,
     *     nullable=true
     * )
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
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @Serializer\Exclude
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     * @Serializer\Exclude
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     mappedBy="property",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     * @Serializer\Exclude
     */
    protected $units;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Invite",
     *     mappedBy="property",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     * @Serializer\Exclude
     */
    protected $invite;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="group_properties"
     * )
     * @Serializer\Exclude
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
     * @Serializer\Exclude
     */
    protected $contracts;

    public function __construct()
    {
        $this->property_groups = new ArrayCollection();
        $this->units = new ArrayCollection();
        $this->contracts = new ArrayCollection();
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
     * Add property_landlord
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return Property
     */
    public function addPropertyGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->property_groups[] = $group;
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
}
