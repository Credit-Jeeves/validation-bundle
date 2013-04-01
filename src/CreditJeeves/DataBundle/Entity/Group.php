<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_account_group")
 */
class Group
{
    /**
     * 
     * @var string
     */
    const TYPE_VEHICLE = 'vehicle';

    /**
     * 
     * @var string
     */
    const TYPE_ESTATE = 'estate';

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
     *
     * @ORM\Column(type="string")
     */
    protected $website_url;

    /**
     * @ORM\ManyToMany(targetEntity="CreditJeeves\DataBundle\Entity\User", mappedBy="dealer_groups")
     */
    protected $group_dealers;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Lead", mappedBy="group")
     */
    protected $leads;

    public function __construct()
    {
        parent::__construct();
        $this->leads         = new ArrayCollection();
        $this->group_dealers = new ArrayCollection();
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
}