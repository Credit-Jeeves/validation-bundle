<?php
namespace CreditJeeves\DataBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
//use JMS\DiExtraBundle\Annotation\Service;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Vehicle;
use CreditJeeves\DataBundle\Entity\Order;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_user")
 *
 */
class User extends BaseUser
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
    protected $first_name;

    /**
     * @ORM\Column(type="string")
     */
    protected $middle_initial;

    /**
     * @ORM\Column(type="string")
     */
    protected $last_name;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $street_address1;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $street_address2;

    /**
     * @ORM\Column(type="string")
     */
    protected $unit_no;

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
     * @ORM\Column(type="bigint")
     */
    protected $phone_type;

    /**
     * @ORM\Column(type="date")
     */
    protected $date_of_birth;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $ssn;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\ReportPrequal", mappedBy="user")
     */
    protected $reportsPrequal;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\ReportD2c", mappedBy="user")
     */
    protected $reportsD2c;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Score", mappedBy="user")
     */
    protected $scores;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Order", mappedBy="user")
     */
    protected $orders;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Lead", mappedBy="user")
     */
    protected $user_leads;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Lead", mappedBy="dealer")
     */
    protected $dealer_leads;
    
    /**
     * @ORM\ManyToMany(targetEntity="CreditJeeves\DataBundle\Entity\Group", inversedBy="group_dealers")
     * @ORM\JoinTable(name="cj_dealer_group",
     *      joinColumns={@ORM\JoinColumn(name="dealer_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    protected $dealer_groups;

    /**
     * 
     * @ORM\OneToOne(targetEntity="CreditJeeves\DataBundle\Entity\Vehicle", mappedBy="user")
     */
    protected $vehicle;

    /**
     * 
     * @var string
     */
    protected $new_password;

    public function __construct()
    {
        parent::__construct();
        $this->reportsPrequal = new ArrayCollection();
        $this->reportsD2c     = new ArrayCollection();
        $this->scores         = new ArrayCollection();
        $this->orders         = new ArrayCollection();
        $this->user_leads     = new ArrayCollection();
        $this->dealer_leads   = new ArrayCollection();
        $this->groups         = new ArrayCollection();
    }

    public function getNewPassword()
    {
        return $this->new_password;
    }
    

    public function setNewPassword($newPassword)
    {
        $this->new_password = $newPassword;
    }

  /**
   * (non-PHPdoc)
   * @see FOS\UserBundle\Model.User::setPassword()
   */
  public function setPassword($password)
  {
    $this->password = md5($password);
  }

  public function getType()
  {
    return $this->type;
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
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }


    /**
     * Add scores
     *
     * @param \CreditJeeves\DataBundle\Entity\Score $scores
     * @return User
     */
    public function addScore(\CreditJeeves\DataBundle\Entity\Score $scores)
    {
        $this->scores[] = $scores;
    
        return $this;
    }

    /**
     * Remove scores
     *
     * @param \CreditJeeves\DataBundle\Entity\Score $scores
     */
    public function removeScore(\CreditJeeves\DataBundle\Entity\Score $scores)
    {
        $this->scores->removeElement($scores);
    }

    /**
     * Get scores
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getScores()
    {
        return $this->scores;
    }



    /**
     * Add reportsPrequal
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal
     * @return User
     */
    public function addReportsPrequal(\CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal)
    {
        $this->reportsPrequal[] = $reportsPrequal;
    
        return $this;
    }

    /**
     * Remove reportsPrequal
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal
     */
    public function removeReportsPrequal(\CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal)
    {
        $this->reportsPrequal->removeElement($reportsPrequal);
    }

    /**
     * Get reportsPrequal
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReportsPrequal()
    {
        return $this->reportsPrequal;
    }

    /**
     * Add reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c
     * @return User
     */
    public function addReportsD2c(\CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c)
    {
        $this->reportsD2c[] = $reportsD2c;
    
        return $this;
    }

    /**
     * Remove reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c
     */
    public function removeReportsD2c(\CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c)
    {
        $this->reportsD2c->removeElement($reportsD2c);
    }

    /**
     * Get reportsD2c
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReportsD2c()
    {
        return $this->reportsD2c;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    
        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set middle_initial
     *
     * @param string $middleInitial
     * @return User
     */
    public function setMiddleInitial($middleInitial)
    {
        $this->middle_initial = $middleInitial;
    
        return $this;
    }

    /**
     * Get middle_initial
     *
     * @return string 
     */
    public function getMiddleInitial()
    {
        return $this->middle_initial;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    
        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * 
     * @param string $ssn
     */
    public function setSsn($ssn)
    {
        $this->ssn = $ssn;
        return $this;
    }

    /**
     * @return string
     */
    public function getSsn()
    {
        return $this->ssn;
    }

    /**
     * @return string
     */
    public function displaySsn()
    {
        $sSSN = substr($this->getSsn(), 0, 5);
    
        return substr($sSSN, 0, 3) . '-' . substr($sSSN, 3) . '-XXXX';
    }

    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    public function setDateOfBirth($sDOB)
    {
        $this->date_of_birth = $sDOB;
        
        return $this;
    }

    /**
     * Here would be logic, how we'll get actice lead. Now - simply last
     *
     * @return Lead
     */
    public function getActiveLead()
    {
        $nLeads = $this->getUserLeads()->count();
        if ($nLeads > 0) {
            return $this->getUserLeads()->last();
        } else {
            return new Lead();
        }
    }


    /**
     * Add dealer_groups
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $dealerGroups
     * @return User
     */
    public function addDealerGroup(\CreditJeeves\DataBundle\Entity\Group $dealerGroups)
    {
        $this->dealer_groups[] = $dealerGroups;

        return $this;
    }

    /**
     * Remove dealer_groups
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $dealerGroups
     */
    public function removeDealerGroup(\CreditJeeves\DataBundle\Entity\Group $dealerGroups)
    {
        $this->dealer_groups->removeElement($dealerGroups);
    }

    /**
     * Get dealer_groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDealerGroups()
    {
        return $this->dealer_groups;
    }

    /**
     * Add user_leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $userLeads
     * @return User
     */
    public function addUserLead(\CreditJeeves\DataBundle\Entity\Lead $userLeads)
    {
        $this->user_leads[] = $userLeads;

        return $this;
    }

    /**
     * Remove user_leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $userLeads
     */
    public function removeUserLead(\CreditJeeves\DataBundle\Entity\Lead $userLeads)
    {
        $this->user_leads->removeElement($userLeads);
    }

    /**
     * Get user_leads
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserLeads()
    {
        return $this->user_leads;
    }


    /**
     * Add dealer_leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $dealerLeads
     * @return User
     */
    public function addDealerLead(\CreditJeeves\DataBundle\Entity\Lead $dealerLeads)
    {
        $this->dealer_leads[] = $dealerLeads;

        return $this;
    }

    /**
     * Remove dealer_leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $dealerLeads
     */
    public function removeDealerLead(\CreditJeeves\DataBundle\Entity\Lead $dealerLeads)
    {
        $this->dealer_leads->removeElement($dealerLeads);
    }

    /**
     * Get dealer_leads
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDealerLeads()
    {
        return $this->dealer_leads;
    }

    /**
     * Set vehicle
     *
     * @param \CreditJeeves\DataBundle\Entity\Vehicle $vehicle
     * @return User
     */
    public function setVehicle(\CreditJeeves\DataBundle\Entity\Vehicle $vehicle = null)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle
     *
     * @return \CreditJeeves\DataBundle\Entity\Vehicle 
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * Set street_address1
     *
     * @param string $streetAddress1
     * @return User
     */
    public function setStreetAddress1($streetAddress1)
    {
        $this->street_address1 = $streetAddress1;
    
        return $this;
    }

    /**
     * Get street_address1
     *
     * @return string 
     */
    public function getStreetAddress1()
    {
        return $this->street_address1;
    }

    /**
     * Set street_address2
     *
     * @param string $streetAddress2
     * @return User
     */
    public function setStreetAddress2($streetAddress2)
    {
        $this->street_address2 = $streetAddress2;
    
        return $this;
    }

    /**
     * Get street_address2
     *
     * @return string 
     */
    public function getStreetAddress2()
    {
        return $this->street_address2;
    }

    /**
     * Set unit_no
     *
     * @param string $unitNo
     * @return User
     */
    public function setUnitNo($unitNo)
    {
        $this->unit_no = $unitNo;
    
        return $this;
    }

    /**
     * Get unit_no
     *
     * @return string 
     */
    public function getUnitNo()
    {
        return $this->unit_no;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return User
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
     * @return User
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
     * @return User
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
     * Set phone_type
     *
     * @param integer $phoneType
     * @return User
     */
    public function setPhoneType($phoneType)
    {
        $this->phone_type = $phoneType;
    
        return $this;
    }

    /**
     * Get phone_type
     *
     * @return integer 
     */
    public function getPhoneType()
    {
        return $this->phone_type;
    }

    /**
     * Add orders
     *
     * @param \CreditJeeves\DataBundle\Entity\Order $orders
     * @return User
     */
    public function addOrder(\CreditJeeves\DataBundle\Entity\Order $orders)
    {
        $this->orders[] = $orders;
    
        return $this;
    }

    /**
     * Remove orders
     *
     * @param \CreditJeeves\DataBundle\Entity\Order $orders
     */
    public function removeOrder(\CreditJeeves\DataBundle\Entity\Order $orders)
    {
        $this->orders->removeElement($orders);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrders()
    {
        return $this->orders;
    }

    public function isCompleteOrderExist()
    {
        $aOrders = $this->getOrders();
        foreach ($aOrders as $Order) {
            $sStatus = $Order->getStatus();
            if ($sStatus == Order::STATUS_COMPLETE) {
                return true;
            }
        }
        return false;
    }
}