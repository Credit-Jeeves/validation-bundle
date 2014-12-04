<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\CoreBundle\Type\Encrypt;
use CreditJeeves\DataBundle\Entity\ReportD2c;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Entity\User as BaseUser;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Enum\UserCulture;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use RentJeeves\CoreBundle\Validator\InviteEmail;
use RentJeeves\DataBundle\Entity\UserSettings;
use RentJeeves\DataBundle\Entity\PartnerCode;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CoreBundle\DateTime;

/**
 * @ORM\MappedSuperclass
 * @UniqueEntity(fields={"email"}, groups={"invite", "import"})
 */
abstract class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(
     *     type="bigint"
     * )
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     * @Serializer\Groups({"RentJeevesImport", "AdminResidentMapping"})
     */
    protected $id;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.user.first_name.empty",
     *     groups={
     *         "user_profile",
     *         "buy_report",
     *         "user_admin",
     *         "invite",
     *         "tenant_invite",
     *         "account_landlord",
     *         "api_identity_check",
     *         "import",
     *         "api"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=255,
     *     minMessage="error.user.first_name.short",
     *     maxMessage="error.user.first_name.long",
     *     groups={
     *         "user_profile",
     *         "buy_report",
     *         "user_admin",
     *         "invite",
     *         "tenant_invite",
     *         "account_landlord",
     *         "api_identity_check",
     *         "import",
     *         "api"
     *     }
     * )
     * @Assert\Regex(
     *     pattern = "/^[a-zA-Z \-'\s]{1,65}$/",
     *     message="regexp.error.name",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $first_name;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @Assert\Length(
     *     min=1,
     *     max=255,
     *     minMessage="error.user.middleInitial.short",
     *     maxMessage="error.user.middleInitial.long",
     *     groups={
     *          "api_identity_check"
     *     }
     * )
     * @Serializer\Type("string")
     */
    protected $middle_initial;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.user.last_name.empty",
     *     groups={
     *         "user_profile",
     *         "buy_report",
     *         "user_admin",
     *         "invite",
     *         "tenant_invite",
     *         "account_landlord",
     *         "api_identity_check",
     *         "import",
     *         "api"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=255,
     *     minMessage="error.user.last_name.short",
     *     maxMessage="error.user.last_name.long",
     *     groups={
     *         "user_profile",
     *         "buy_report",
     *         "user_admin",
     *         "invite",
     *         "tenant_invite",
     *         "account_landlord",
     *         "api_identity_check",
     *         "import",
     *         "api"
     *     }
     * )
     * @Assert\Regex(
     *     pattern = "/^[a-zA-Z \-'\s]{1,65}$/",
     *     message="regexp.error.name",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     * @Serializer\Type("string")
     */
    protected $last_name;

    /**
     * @Assert\NotBlank(
     *     message="email.required",
     *     groups={
     *         "user_admin",
     *         "invite",
     *         "invitationApi",
     *         "tenant_invite",
     *         "api"
     *     }
     * )
     * @Assert\Email(
     *     groups={
     *         "user_admin",
     *         "invite",
     *         "invitationApi",
     *         "tenant_invite",
     *         "account_landlord",
     *         "api_identity_check",
     *         "import",
     *         "api"
     *     }
     * )
     * @InviteEmail(
     *     groups={
     *         "invite",
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport", "AdminResidentMapping"})
     * @Serializer\Type("string")
     */
    protected $email;

    /**
     * @ORM\Column(type="encrypt", nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.address.empty",
     *     groups={
     *         "user_address",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "user_address",
     *         "buy_report"
     *     }
     * )
     *
     * @deprecated would be removed in 2.2
     */
    protected $street_address1;

    /**
     * @ORM\Column(type="encrypt", nullable=true)
     */
    protected $street_address2;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     * @Assert\Length(
     *     max=31,
     *     groups={
     *         "user_address"
     *     }
     * )
     *
     * @deprecated would be removed in 2.2
     */
    protected $unit_no;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.city.empty",
     *     groups={
     *         "buy_report"
     *     }
     * )
     *
     * @deprecated would be removed in 2.2
     */
    protected $city;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.state.empty",
     *     groups={
     *         "user_address",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "user_address",
     *         "buy_report"
     *     }
     * )
     *
     * @deprecated would be removed in 2.2
     */
    protected $state;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.zip.empty",
     *     groups={
     *         "user_address",
     *         "buy_report"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=15,
     *     maxMessage = "Zip code cannot be longer than {{ limit }} characters length",
     *     groups={
     *         "user_address",
     *         "buy_report"
     *     }
     * )
     *
     * @deprecated would be removed in 2.2
     */
    protected $zip;

    /**
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "api_identity_check"
     *     }
     * )
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $phone_type;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Assert\Regex(
     *     pattern = "/^(\(\d{3}\) ?\d{3}-|\d{3}\.\d{3}\.|\d{3}-?\d{3}-?)\d{4}$/",
     *     message="error.user.phone.format",
     *     groups={
     *         "user_admin",
     *         "user_profile",
     *         "account_landlord",
     *         "invite",
     *         "tenant_invite",
     *         "invitationApi",
     *         "api",
     *         "import"
     *     }
     * )
     */
    protected $phone;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(
     *     message="error.user.date",
     *     groups={
     *         "user_profile",
     *         "invite_short"
     *     }
     * )
     * @Assert\NotBlank(
     *     message="error.user.date.empty",
     *     groups={
     *         "user_profile",
     *         "invite_short",
     *         "birth_and_ssn"
     *     }
     * )
     *
     */
    protected $date_of_birth;

    /**
     * @ORM\Column(type="encrypt", nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.ssn.empty",
     *     groups={
     *         "user_profile",
     *         "birth_and_ssn"
     *     }
     * )
     * @Assert\Length(
     *     min=9,
     *     max=9,
     *     exactMessage="error.user.ssn.exact",
     *     groups={
     *         "user_profile",
     *         "birth_and_ssn",
     *         "api_identity_check"
     *     }
     * )
     * @Serializer\Type("string")
     */
    protected $ssn;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default"="0"})
     */
    protected $is_active = false;

    /**
     * @ORM\Column(type="string", length=128, nullable=true, unique=true)
     */
    protected $invite_code;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default"="1"})
     */
    protected $score_changed_notification = true;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default"="0"})
     */
    protected $offer_notification = true;

    /**
     * @ORM\Column(type="UserCulture", options={"default"="en"})
     */
    protected $culture = UserCulture::EN;

    /**
     * @ORM\Column(type="boolean", options={"default"="1"})
     */
    protected $has_data = true;

    /**
     * @ORM\Column(type="UserIsVerified", options={"default"="none"})
     */
    protected $is_verified = UserIsVerified::NONE;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $has_report;

    /**
     */
    protected $type;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $holding_id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="users"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id"
     * )
     */
    protected $holding;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default"="0"})
     */
    protected $is_holding_admin = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default"="0"})
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Report",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $reports;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Score",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $scores;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Tradeline",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $tradelines;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\ApplicantIncentive",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $incentives;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $orders;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Lead",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $user_leads;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Lead",
     *     mappedBy="dealer",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $dealer_leads;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="group_dealers"
     * )
     * @ORM\JoinTable(
     *      name="cj_dealer_group",
     *      joinColumns={@ORM\JoinColumn(name="dealer_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $dealer_groups;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="dealers",
     *     cascade={"remove"},
     *     orphanRemoval=true
     * )
     */
    protected $dealer_to_groups;

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
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\LoginDefense",
     *     mappedBy="user",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $defense;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Pidkiq",
     *      mappedBy="user",
     *      cascade={"persist", "remove", "merge"},
     *      orphanRemoval=true
     * )
     */
    protected $pidkiqs;

    /**
     * @ORM\OneToMany(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Address",
     *      mappedBy="user",
     *      cascade={"persist", "remove", "merge"}
     * )
     * @var ArrayCollection
     */
    protected $addresses;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\GroupAffiliate",
     *     mappedBy="user"
     * )
     *
     * @var ArrayCollection
     */
    protected $group_affilate;


    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\AccessToken",
     *     mappedBy="user",
     *     cascade={"all"}
     * )
     */
    protected $accessTokens;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\AuthCode",
     *     mappedBy="user",
     *     cascade={"all"}
     * )
     */
    protected $authCodes;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\RefreshToken",
     *     mappedBy="user",
     *     cascade={"all"}
     * )
     */
    protected $refreshTokens;


    /**
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\ApiUpdate",
     *     mappedBy="user",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $apiUpdate;

    /**
     * @ORM\OneToOne(
     *      targetEntity="\RentJeeves\DataBundle\Entity\UserSettings",
     *      mappedBy="user",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     */
    protected $settings;

    /**
     * @ORM\OneToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\PartnerCode",
     *      mappedBy="user",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     */
    protected $partnerCode;

    /**
     * @ORM\Column(
     *     type = "string",
     *     name = "last_ip",
     *     nullable = true,
     *     length = 35
     * )
     */
    protected $lastIp;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\PartnerUserMapping",
     *     mappedBy="user",
     *     cascade={"all"}
     * )
     */
    protected $partner;

    public function __construct()
    {
        parent::__construct();
        $this->reports = new ArrayCollection();
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
        $this->addresses = new ArrayCollection();
        $this->group_affilate = new ArrayCollection();
        $this->accessTokens = new ArrayCollection();
        $this->authCodes = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
        $this->created_at = new DateTime();
    }


    /**
     * @param mixed $settings
     */
    public function setSettings(UserSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return UserSettings
     */
    public function getSettings()
    {
        if (!$this->settings) {
            $this->settings = new UserSettings();
            $this->settings->setUser($this);
        }

        return $this->settings;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $apiUpdate
     */
    public function setApiUpdate(\CreditJeeves\DataBundle\Entity\ApiUpdate $apiUpdate)
    {
        $this->apiUpdate = $apiUpdate;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getApiUpdate()
    {
        return $this->apiUpdate;
    }



    public function getRoles()
    {

        if (!empty($this->roles)) {
            return $this->roles;
        }

        switch ($this->getType()) {
            case UserType::APPLICANT:
                return array('ROLE_USER');
            case UserType::DEALER:
                return array('ROLE_DEALER');
            case UserType::ADMIN:
                return array(
                    'ROLE_USER',
                    'ROLE_DEALER',
                    'ROLE_ADMIN',
                    'ROLE_TENANT',
                    'ROLE_LANDLORD',
                    'ROLE_PARTNER'
                );
            case UserType::TETNANT:
                return array('ROLE_TENANT');
            case UserType::LANDLORD:
                return array('ROLE_LANDLORD');
            case UserType::PARTNER:
                return array('ROLE_PARTNER');
        }
        throw new \RuntimeException(sprintf("Wrong type '%s'", $this->getType()));
    }

    /**
     * Get id
     *
     * @param int $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     *
     * @deprecated would be removed in 2.2
     *
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
     * @deprecated would be removed in 2.2
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
     *
     * @deprecated would be removed in 2.2
     *
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
     * @deprecated would be removed in 2.2
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
     *
     * @deprecated would be removed in 2.2
     *
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
     * @deprecated would be removed in 2.2
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
     *
     * @deprecated would be removed in 2.2
     *
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
     * @deprecated would be removed in 2.2
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
     *
     * @deprecated would be removed in 2.2
     *
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
     * @deprecated would be removed in 2.2
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
     *
     * @deprecated would be removed in 2.2
     *
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
     * @deprecated would be removed in 2.2
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
     * Add report
     *
     * @param Report $report
     * @return User
     */
    public function addReport(Report $report)
    {
        $this->reports[] = $report;

        return $this;
    }

    /**
     * Remove report
     *
     * @param Report $report
     */
    public function removeReport(Report $report)
    {
        $this->reports->removeElement($report);
    }

    /**
     * Get reports
     *
     * @return Collection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Add reportsPrequal
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal
     * @return User
     */
    public function addReportsPrequal(\CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal)
    {
        $this->reports[] = $reportsPrequal;

        return $this;
    }

    /**
     * Remove reportsPrequal
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal
     */
    public function removeReportsPrequal(\CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal)
    {
        $this->reports->removeElement($reportsPrequal);
    }

    /**
     * Get reportsPrequal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReportsPrequal()
    {
        return $this->reports->filter(
            function (Report $report) {
                if ($report instanceof ReportPrequal) {
                    return true;
                }
                return false;
            }
        );
    }

    /**
     * Add reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c
     * @return User
     */
    public function addReportsD2c(\CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c)
    {
        $this->reports[] = $reportsD2c;

        return $this;
    }

    /**
     * Remove reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c
     */
    public function removeReportsD2c(\CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c)
    {
        $this->reports->removeElement($reportsD2c);
    }

    /**
     * Get reportsD2c
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReportsD2c()
    {
        return $this->reports->filter(
            function (Report $report) {
                if ($report instanceof ReportD2c) {
                    return true;
                }
                return false;
            }
        );
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
     * Set defense
     *
     * @param \CreditJeeves\DataBundle\Entity\LoginDefense $defense
     * @return User
     */
    public function setDefense(\CreditJeeves\DataBundle\Entity\LoginDefense $defense = null)
    {
        $this->defense = $defense;
    
        return $this;
    }
    
    /**
     * Get defense
     *
     * @return \CreditJeeves\DataBundle\Entity\LoginDefense
     */
    public function getDefense()
    {
        return $this->defense;
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

    public function setHolding(\CreditJeeves\DataBundle\Entity\Holding $holding = null)
    {
        $this->holding = $holding;
        return $this;
    }

    /**
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * Add address
     *
     * @param \CreditJeeves\DataBundle\Entity\Address $pidkiqs
     * @return User
     */
    public function addAddress(\CreditJeeves\DataBundle\Entity\Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove Address
     *
     * @param \CreditJeeves\DataBundle\Entity\Address $address
     */
    public function removeAddress(\CreditJeeves\DataBundle\Entity\Address $address)
    {
        $this->addresses->removeElement($address);
    }

    /**
     * Get Addresses
     *
     * @param ArrayCollection $addresses
     *
     * @return Address
     */
    public function setAddresses(ArrayCollection $addresses = null)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * Get Addresses
     *
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * Add group_affilate
     *
     * @param \CreditJeeves\DataBundle\Entity\GroupAffiliate $groupAffilate
     * @return User
     */
    public function addGroupAffilate(\CreditJeeves\DataBundle\Entity\GroupAffiliate $groupAffilate)
    {
        $this->group_affilate[] = $groupAffilate;
    
        return $this;
    }

    /**
     * Remove group_affilate
     *
     * @param \CreditJeeves\DataBundle\Entity\GroupAffiliate $groupAffilate
     */
    public function removeGroupAffilate(\CreditJeeves\DataBundle\Entity\GroupAffiliate $groupAffilate)
    {
        $this->group_affilate->removeElement($groupAffilate);
    }

    /**
     * Get group_affilate
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupAffilate()
    {
        return $this->group_affilate;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\AccessToken $accessToken
     * @return User
     */
    public function addAccessToken(\CreditJeeves\DataBundle\Entity\AccessToken $accessToken)
    {
        $this->accessTokens[] = $accessToken;
        return $this;
    }

    /**
     * Remove accessToken
     *
     * @param \CreditJeeves\DataBundle\Entity\AccessToken $accessToken
     */
    public function removeAccessToken(\CreditJeeves\DataBundle\Entity\AccessToken $accessToken)
    {
        $this->accessTokens->removeElement($accessToken);
    }

    /**
     * Get accessTokens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccessTokens()
    {
        return $this->accessTokens;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\RefreshToken $refreshToken
     * @return User
     */
    public function addRefreshToken(\CreditJeeves\DataBundle\Entity\RefreshToken $refreshToken)
    {
        $this->resfreshTokens[] = $refreshToken;
        return $this;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\RefreshToken $refreshToken
     */
    public function removeRefreshToken(\CreditJeeves\DataBundle\Entity\RefreshToken $refreshToken)
    {
        $this->refreshTokens->removeElement($refreshToken);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRefreshTokens()
    {
        return $this->refreshTokens;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\AuthToken $authToken
     * @return User
     */
    public function addAuthCode(\CreditJeeves\DataBundle\Entity\AuthCode $authCode)
    {
        $this->authCodes[] = $authCode;
        return $this;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\AuthToken $authCode
     */
    public function removeAuthCode(\CreditJeeves\DataBundle\Entity\AuthCode $authCode)
    {
        $this->authCodes->removeElement($authCode);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthCodes()
    {
        return $this->authCodes;
    }

    /**
     * @param PartnerCode $partnerCode
     */
    public function setPartnerCode(PartnerCode $partnerCode)
    {
        $this->partnerCode = $partnerCode;
    }

    /**
     * @return PartnerCode
     */
    public function getPartnerCode()
    {
        return $this->partnerCode;
    }

    /**
     * @param string $lastIp
     */
    public function setLastIp($lastIp)
    {
        $this->lastIp = $lastIp;
    }

    /**
     * @return string
     */
    public function getLastIp()
    {
        return $this->lastIp;
    }
}
