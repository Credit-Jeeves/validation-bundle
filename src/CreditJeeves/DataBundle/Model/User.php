<?php
namespace CreditJeeves\DataBundle\Model;

use FOS\UserBundle\Entity\User as BaseUser;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Enum\UserCulture;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     */
    protected $first_name;

    /**
     * @ORM\Column(type="string")
     */
    protected $middle_initial;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     */
    protected $last_name;

    /**
     * @ORM\Column(type="encrypt")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     */
    protected $street_address1;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $street_address2;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=31,
     *     groups={
     *         "registration_new"
     *     }
     * )
     */
    protected $unit_no;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     groups={
     *         "buy_report"
     *     }
     * )
     */
    protected $city;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     */
    protected $state;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=15,
     *     maxMessage = "Zip code cannot be longer than {{ limit }} characters length",
     *     groups={
     *         "registration_new",
     *         "buy_report"
     *     }
     * )
     */
    protected $zip;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $phone_type;

    /**
     * @ORM\Column(type="string")
     */
    protected $phone;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new"
     *     }
     * )
     */
    protected $date_of_birth;

    /**
     * @ORM\Column(type="encrypt")
     * @Assert\NotBlank(
     *     groups={
     *         "registration_new"
     *     }
     * )
     * @Assert\Length(
     *     min=9,
     *     max=9,
     *     groups={
     *         "registration_new"
     *     }
     * )
     */
    protected $ssn;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_active;

    /**
     * @ORM\Column(type="string")
     */
    protected $invite_code;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $score_changed_notification = true;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $offer_notification = true;

    /**
     * @ORM\Column(type="UserCulture")
     */
    protected $culture = UserCulture::EN;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $has_data = false;

    /**
     * @ORM\Column(type="UserIsVerified")
     */
    protected $is_verified = UserIsVerified::NONE;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $has_report = false;

    /**
     * @ORM\Column(type="UserType")
     */
    protected $type = 'applicant';

    /**
     * @ORM\Column(type="bigint")
     */
    protected $holding_id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_holding_admin = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_super_admin = false;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\ReportPrequal",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $reportsPrequal;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\ReportD2c",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $reportsD2c;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Score",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $scores;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Tradeline",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $tradelines;
    
    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\ApplicantIncentive",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $incentives;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $orders;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Lead",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $user_leads;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Lead",
     *     mappedBy="dealer",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $dealer_leads;

    /**
     * @ORM\ManyToMany(targetEntity="\CreditJeeves\DataBundle\Entity\Group", inversedBy="group_dealers")
     * @ORM\JoinTable(
     *      name="cj_dealer_group",
     *      joinColumns={@ORM\JoinColumn(name="dealer_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $dealer_groups;

    /**
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Vehicle",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $vehicle;


    /**
     * @ORM\OneToMany(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Pidkiq",
     *      mappedBy="user"
     * )
     */
    protected $pidkiqs;

    public function __construct()
    {
        parent::__construct();
        $this->reportsPrequal = new ArrayCollection();
        $this->reportsD2c = new ArrayCollection();
        $this->scores = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->user_leads = new ArrayCollection();
        $this->dealer_leads = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->pidkiqs = new ArrayCollection();
        $this->incentives = new ArrayCollection();
        $this->tradelines = new ArrayCollection();
    }

    public function getRoles()
    {
        $sType = $this->getType();
        switch ($sType) {
            case 'applicant':
                return array('ROLE_USER');
                break;
            case 'dealer':
                return array('ROLE_DEALER');
                break;
            case 'admin':
                return array('ROLE_USER', 'ROLE_DEALER', 'ROLE_ADMIN');
                break;
        }
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
     * Set street_address1
     *
     * @param encrypt $streetAddress1
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
     * @return encrypt
     */
    public function getStreetAddress1()
    {
        return $this->street_address1;
    }

    /**
     * Set street_address2
     *
     * @param encrypt $streetAddress2
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
     * @return encrypt
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
     * Set phone
     *
     * @param string $phone
     * @return User
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
     * Set date_of_birth
     *
     * @param \DateTime $dateOfBirth
     * @return User
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->date_of_birth = $dateOfBirth;

        return $this;
    }

    /**
     * Get date_of_birth
     *
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    /**
     * Set ssn
     *
     * @param encrypt $ssn
     * @return User
     */
    public function setSsn($ssn)
    {
        $this->ssn = $ssn;

        return $this;
    }

    /**
     * Get ssn
     *
     * @return encrypt
     */
    public function getSsn()
    {
        return $this->ssn;
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set invite_code
     *
     * @param string $inviteCode
     * @return User
     */
    public function setInviteCode($inviteCode)
    {
        $this->invite_code = $inviteCode;

        return $this;
    }

    /**
     * Get invite_code
     *
     * @return string
     */
    public function getInviteCode()
    {
        return $this->invite_code;
    }

    /**
     * Set score_changed_notification
     *
     * @param boolean $scoreChangedNotification
     * @return User
     */
    public function setScoreChangedNotification($scoreChangedNotification)
    {
        $this->score_changed_notification = $scoreChangedNotification;

        return $this;
    }

    /**
     * Get score_changed_notification
     *
     * @return boolean
     */
    public function getScoreChangedNotification()
    {
        return $this->score_changed_notification;
    }

    /**
     * Set offer_notification
     *
     * @param boolean $offerNotification
     * @return User
     */
    public function setOfferNotification($offerNotification)
    {
        $this->offer_notification = $offerNotification;

        return $this;
    }

    /**
     * Get offer_notification
     *
     * @return boolean
     */
    public function getOfferNotification()
    {
        return $this->offer_notification;
    }

    /**
     * Set culture
     *
     * @param UserCulture $culture
     * @return User
     */
    public function setCulture($culture)
    {
        $this->culture = $culture;

        return $this;
    }

    /**
     * Get culture
     *
     * @return UserCulture
     */
    public function getCulture()
    {
        return $this->culture;
    }

    /**
     * Set has_data
     *
     * @param boolean $hasData
     * @return User
     */
    public function setHasData($hasData)
    {
        $this->has_data = $hasData;

        return $this;
    }

    /**
     * Get has_data
     *
     * @return boolean
     */
    public function getHasData()
    {
        return $this->has_data;
    }

    /**
     * Set is_verified
     *
     * @param UserIsVerified $isVerified
     * @return User
     */
    public function setIsVerified($isVerified)
    {
        $this->is_verified = $isVerified;

        return $this;
    }

    /**
     * Get is_verified
     *
     * @return UserIsVerified
     */
    public function getIsVerified()
    {
        return $this->is_verified;
    }

    /**
     * Set has_report
     *
     * @param boolean $hasReport
     * @return User
     */
    public function setHasReport($hasReport)
    {
        $this->has_report = $hasReport;

        return $this;
    }

    /**
     * Get has_report
     *
     * @return boolean
     */
    public function getHasReport()
    {
        return $this->has_report;
    }

    /**
     * Set type
     *
     * @param UserType $type
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return UserType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set holding_id
     *
     * @param integer $holdingId
     * @return User
     */
    public function setHoldingId($holdingId)
    {
        $this->holding_id = $holdingId;

        return $this;
    }

    /**
     * Get holding_id
     *
     * @return integer
     */
    public function getHoldingId()
    {
        return $this->holding_id;
    }

    /**
     * Set is_holding_admin
     *
     * @param boolean $isHoldingAdmin
     * @return User
     */
    public function setIsHoldingAdmin($isHoldingAdmin)
    {
        $this->is_holding_admin = $isHoldingAdmin;

        return $this;
    }

    /**
     * Get is_holding_admin
     *
     * @return boolean
     */
    public function getIsHoldingAdmin()
    {
        return $this->is_holding_admin;
    }

    /**
     * Set is_super_admin
     *
     * @param boolean $isSuperAdmin
     * @return User
     */
    public function setIsSuperAdmin($isSuperAdmin)
    {
        $this->is_super_admin = $isSuperAdmin;

        return $this;
    }

    /**
     * Get is_super_admin
     *
     * @return boolean
     */
    public function getIsSuperAdmin()
    {
        return $this->is_super_admin;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
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
     * @return User
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
     * Add pidkiqs
     *
     * @param \CreditJeeves\DataBundle\Entity\Pidkiq $pidkiqs
     * @return User
     */
    public function addPidkiq(\CreditJeeves\DataBundle\Entity\Pidkiq $pidkiqs)
    {
        $this->pidkiqs[] = $pidkiqs;

        return $this;
    }

    /**
     * Remove pidkiqs
     *
     * @param \CreditJeeves\DataBundle\Entity\Pidkiq $pidkiqs
     */
    public function removePidkiq(\CreditJeeves\DataBundle\Entity\Pidkiq $pidkiqs)
    {
        $this->pidkiqs->removeElement($pidkiqs);
    }

    /**
     * Get pidkiqs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPidkiqs()
    {
        return $this->pidkiqs;
    }
}
