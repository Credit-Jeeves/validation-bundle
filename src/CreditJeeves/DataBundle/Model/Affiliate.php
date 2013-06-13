<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class Affiliate
{
    /**
     * @ORM\Column(
     *     name="name",
     *     type="string",
     *     length=255
     * )
     * @Assert\NotBlank(
     *     groups={
     *         "affiliate"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "affiliate",
     *     }
     * )
     */
    protected $name;

    /**
     * @ORM\Column(
     *     name="phone",
     *     type="string",
     *     length=255
     * )
     */
    protected $phone;

    /**
     * @ORM\Column(
     *     name="fax",
     *     type="string",
     *     length=255
     * )
     */
    protected $fax;

    /**
     * @ORM\Column(name="street_address1", type="string", length=255)
     */
    protected $streetAddress1;

    /**
     * @ORM\Column(name="street_address2", type="string", length=255)
     */
    protected $streetAddress2;

    /**
     * @ORM\Column(name="city", type="string", length=255)
     */
    protected $city;

    /**
     * @ORM\Column(name="state", type="string", length=7)
     */
    protected $state;

    /**
     * @ORM\Column(name="zip", type="string", length=15)
     */
    protected $zip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="affiliate",
     *     cascade={
     *         "remove",
     *         },
     *     orphanRemoval=true
     * )
     */
    protected $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->createdAt = new \DateTime();
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
     * Set name
     *
     * @param string $name
     * @return Affiliate
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Affiliate
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return Affiliate
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    
        return $this;
    }

    /**
     * Get fax
     *
     * @return string 
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set streetAddress1
     *
     * @param string $streetAddress1
     * @return Affiliate
     */
    public function setStreetAddress1($streetAddress1)
    {
        $this->streetAddress1 = $streetAddress1;
    
        return $this;
    }

    /**
     * Get streetAddress1
     *
     * @return string 
     */
    public function getStreetAddress1()
    {
        return $this->streetAddress1;
    }

    /**
     * Set streetAddress2
     *
     * @param string $streetAddress2
     * @return Affiliate
     */
    public function setStreetAddress2($streetAddress2)
    {
        $this->streetAddress2 = $streetAddress2;
    
        return $this;
    }

    /**
     * Get streetAddress2
     *
     * @return string 
     */
    public function getStreetAddress2()
    {
        return $this->streetAddress2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Affiliate
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
     * Set state
     *
     * @param string $state
     * @return Affiliate
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Affiliate
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Affiliate
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
     * @return Affiliate
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
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $groups
     * @return Holding
     */
    public function addGroup(\CreditJeeves\DataBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;
        return $this;
    }

    /**
     * Remove groups
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $groups
     */
    public function removeGroup(\CreditJeeves\DataBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
