<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $target_score;

    /**
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     *
     * @ORM\Column(type="string")
     */
    protected $website_url;

    /**
     * @ORM\Column(type="string")
     */
    protected $logo_url;

    /**
     * @ORM\Column(type="text")
     */
    protected $phone;

    /**
     * @ORM\Column(type="string")
     */
    protected $fax;

    /**
     * @ORM\Column(type="string")
     */
    protected $street_address_1;

    /**
     * @ORM\Column(type="string")
     */
    protected $street_address_2;

    /**
     * @ORM\Column(type="string")
     */
    protected $city;

    /**
     * @ORM\Column(type="string")
     */
    protected $state;

    /**
     * @ORM\Column(type="string")
     */
    protected $zip;

    /**
     * @ORM\Column(type="string")
     */
    protected $fee_type;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="CreditJeeves\DataBundle\Entity\User", mappedBy="dealer_groups")
     */
    protected $group_dealers;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Lead", mappedBy="group")
     */
    protected $leads;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\GroupIncentive", mappedBy="group")
     */
    protected $incentives;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="groups"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id"
     * )
     */
    protected $holding;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Affiliate",
     *     inversedBy="groups"
     * )
     * @ORM\JoinColumn(
     *     name="cj_affiliate_id",
     *     referencedColumnName="id"
     * )
     */
    protected $affiliate;
    

    public function __construct()
    {
        $this->leads = new ArrayCollection();
        $this->group_dealers = new ArrayCollection();
        $this->incentives = new ArrayCollection();
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
     * Set type
     *
     * @param string $type
     * @return Group
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name
     *
     * @param string $type
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $leads
     * @return Group
     */
    public function addLead(\CreditJeeves\DataBundle\Entity\Lead $leads)
    {
        $this->leads[] = $leads;

        return $this;
    }

    /**
     * Remove leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $leads
     */
    public function removeLead(\CreditJeeves\DataBundle\Entity\Lead $leads)
    {
        $this->leads->removeElement($leads);
    }

    /**
     * Get leads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeads()
    {
        return $this->leads;
    }

    
    /**
     * Add incentive
     *
     * @param \CreditJeeves\DataBundle\Entity\GroupIncentive $incentive
     * @return Group
     */
    public function addGroupIncentive(\CreditJeeves\DataBundle\Entity\GroupIncentive $incentive)
    {
        $this->incentives[] = $incentive;
    
        return $this;
    }
    
    /**
     * Remove leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $leads
     */
    public function removeGroupIncentive(\CreditJeeves\DataBundle\Entity\GroupIncentive $incentive)
    {
        $this->incentives->removeElement($incentive);
    }
    
    /**
     * Get leads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupIncentives()
    {
        return $this->incentives;
    }

    /**
     * Add group_dealers
     *
     * @param \CreditJeeves\DataBundle\Entity\User $groupDealers
     * @return Group
     */
    public function addGroupDealer(\CreditJeeves\DataBundle\Entity\User $groupDealers)
    {
        $this->group_dealers[] = $groupDealers;

        return $this;
    }

    /**
     * Remove group_dealers
     *
     * @param \CreditJeeves\DataBundle\Entity\User $groupDealers
     */
    public function removeGroupDealer(\CreditJeeves\DataBundle\Entity\User $groupDealers)
    {
        $this->group_dealers->removeElement($groupDealers);
    }

    /**
     * Get group_dealers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupDealers()
    {
        return $this->group_dealers;
    }

    /**
     * Set website_url
     *
     * @param string $websiteUrl
     * @return Group
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->website_url = $websiteUrl;

        return $this;
    }

    /**
     * Get website_url
     *
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->website_url;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Group
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Group
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
     * Set logo_url
     *
     * @param string $logoUrl
     * @return Group
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logo_url = $logoUrl;
    
        return $this;
    }

    /**
     * Get logo_url
     *
     * @return string 
     */
    public function getLogoUrl()
    {
        return $this->logo_url;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return Group
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
     * Set street_address_1
     *
     * @param string $streetAddress1
     * @return Group
     */
    public function setStreetAddress1($streetAddress1)
    {
        $this->street_address_1 = $streetAddress1;
    
        return $this;
    }

    /**
     * Get street_address_1
     *
     * @return string 
     */
    public function getStreetAddress1()
    {
        return $this->street_address_1;
    }

    /**
     * Set street_address_2
     *
     * @param string $streetAddress2
     * @return Group
     */
    public function setStreetAddress2($streetAddress2)
    {
        $this->street_address_2 = $streetAddress2;
    
        return $this;
    }

    /**
     * Get street_address_2
     *
     * @return string 
     */
    public function getStreetAddress2()
    {
        return $this->street_address_2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Group
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
     * @return Group
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
     * @return Group
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

    public function setFeeType($type)
    {
        $this->fee_type = $type;
        return $this;
    }

    public function getFeeType()
    {
        return $this->fee_type;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Group
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set target_score
     *
     * @param integer $targetScore
     * @return Group
     */
    public function setTargetScore($targetScore)
    {
        $this->target_score = $targetScore;

        return $this;
    }

    /**
     * Get target_score
     *
     * @return integer
     */
    public function getTargetScore()
    {
        return $this->target_score;
    }

    public function setHolding(\CreditJeeves\DataBundle\Entity\Holding $holding = null)
    {
        $this->holding = $holding;
        return $this;
    }

    public function getHolding()
    {
        return $this->holding;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
