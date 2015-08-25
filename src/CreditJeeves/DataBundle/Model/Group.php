<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\GroupFeeType;
use CreditJeeves\DataBundle\Enum\GroupType;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\AciCollectPayGroupProfile;
use RentJeeves\DataBundle\Entity\AciImportProfileMap;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Entity\ImportSummary;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\GroupAccountNumberMapping;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 */
abstract class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"paymentAccounts", "AdminProperty"});
     */
    protected $id;

    /**
     * @ORM\Column(
     *     type="bigint",
     *     nullable=true
     * )
     */
    protected $cj_affiliate_id;

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

    /**
     * @ORM\Column(
     *     type="bigint",
     *     nullable=true
     * )
     */
    protected $holding_id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="groups",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id"
     * )
     */
    protected $holding;

    /**
     * @ORM\Column(
     *     type="bigint",
     *     nullable=true
     * )
     */
    protected $parent_id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(
     *     name="parent_id",
     *     referencedColumnName="id"
     * )
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="parent"
     * )
     */
    protected $children;

    /**
     * @ORM\Column(
     *     type="bigint",
     *     nullable=true
     * )
     */
    protected $dealer_id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     inversedBy="dealer_to_groups"
     * )
     * @ORM\JoinColumn(
     *     name="dealer_id",
     *     referencedColumnName="id"
     * )
     */
    protected $dealers;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups({"AdminProperty"});
     *
     * @Assert\NotBlank(groups={"landlordImport"})
     */
    protected $name;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $target_score;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $website_url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $logo_url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fax;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\NotBlank(groups={"landlordImport"})
     */
    protected $street_address_1;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $street_address_2;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\NotBlank(groups={"landlordImport"})
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true, length=7)
     *
     * @Assert\NotBlank(groups={"landlordImport"})
     */
    protected $state;

    /**
     * @ORM\Column(type="string", nullable=true, length=15)
     *
     * @Assert\NotBlank(groups={"landlordImport"})
     */
    protected $zip;

    /**
     * @ORM\Column(
     *     type="GroupFeeType",
     *     options={"default"="flat"}
     * )
     */
    protected $fee_type = GroupFeeType::FLAT;

    /**
     * @ORM\Column(
     *     type="text",
     *     nullable=true
     * )
     */
    protected $contract;

    /**
     * @ORM\Column(
     *     type="date",
     *     nullable=true
     * )
     */
    protected $contract_date;

    /**
     * @ORM\Column(
     *     type="GroupType",
     *     options={"default"="vehicle"}
     * )
     */
    protected $type = GroupType::VEHICLE;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @var boolean
     *
     * @ORM\Column(name="disable_credit_card", type="boolean", options={"default"="0"})
     */
    protected $disableCreditCard;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Lead",
     *     mappedBy="group"
     * )
     */
    protected $leads;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\GroupIncentive",
     *     mappedBy="group"
     * )
     */
    protected $incentives;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     mappedBy="dealer_groups"
     * )
     */
    protected $group_dealers;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Landlord",
     *     mappedBy="agent_groups"
     * )
     */
    protected $group_agents;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="\RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="property_groups"
     * )
     * @ORM\JoinTable(
     *      name="rj_group_property",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="property_id", referencedColumnName="id")}
     * )
     */
    protected $group_properties;

    /**
    * @ORM\OneToMany(
    *     targetEntity="CreditJeeves\DataBundle\Entity\GroupAffiliate",
    *     mappedBy="group"
    * )
    */
    protected $group_affilate;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Tradeline",
     *     mappedBy="group"
     * )
     */
    protected $tradelines;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     mappedBy="group",
     *     cascade={"remove", "persist"},
     *     orphanRemoval=true
     * )
     */
    protected $units;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     mappedBy="group",
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
     *     targetEntity="RentJeeves\DataBundle\Entity\ImportSummary",
     *     mappedBy="group",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     */
    protected $importSummaries;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\RentJeeves\DataBundle\Entity\DepositAccount",
     *     mappedBy="group",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     *
     * @var ArrayCollection
     */
    protected $depositAccounts;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\GroupPhone",
     *     mappedBy="group",
     *     cascade={"persist", "remove", "merge"}
     * )
     */
    protected $groupPhones;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\BillingAccount",
     *     mappedBy="group",
     *     cascade={"persist", "remove", "merge"}
     * )
     */
    protected $billingAccounts;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\GroupSettings",
     *     mappedBy="group",
     *     cascade={"persist", "remove", "merge"},
     *     fetch="EAGER"
     * )
     */
    protected $groupSettings;

    /**
     * @var AciCollectPayGroupProfile
     *
     * @ORM\OneToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\AciCollectPayGroupProfile",
     *      mappedBy="group",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     */
    protected $aciCollectPayProfile;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ContractWaiting",
     *     mappedBy="group",
     *     cascade={"persist", "remove", "merge"}
     * )
     */
    protected $waitingContracts;

    /**
    * @ORM\Column(
     *     type="string",
     *     name="statement_descriptor",
     *     nullable=true
     * )
     */
    protected $statementDescriptor;

    /**
     * @ORM\OneToOne(
     *     targetEntity="\RentJeeves\DataBundle\Entity\GroupAccountNumberMapping",
     *     mappedBy="group",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @Assert\Valid
     * @var GroupAccountNumberMapping
     */
    protected $accountNumberMapping;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="mailing_address_name",
     *      type="string",
     *      nullable=true
     * )
     *
     * @Assert\NotBlank(groups={"landlordImport"})
     */
    protected $mailingAddressName;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="order_algorithm",
     *      type="OrderAlgorithmType",
     *      nullable=false
     * )
     */
    protected $orderAlgorithm = OrderAlgorithmType::SUBMERCHANT;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\AciImportProfileMap",
     *     mappedBy="group"
     * )
     *
     * @var AciImportProfileMap
     */
    protected $aciImportProfileMap;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="external_group_id", nullable=true)
     *
     * @Assert\NotBlank(groups={"landlordImport"})
     */
    protected $externalGroupId;

    public function __construct()
    {
        $this->leads = new ArrayCollection();
        $this->group_dealers = new ArrayCollection();
        $this->group_agents = new ArrayCollection();
        $this->incentives = new ArrayCollection();
        $this->children = new ArrayCollection();

        // TODO move to RentJeeves
        $this->group_properties = new ArrayCollection();
        $this->units = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->groupPhones = new ArrayCollection();
        $this->billingAccounts = new ArrayCollection();
        $this->waitingContracts = new ArrayCollection();
        $this->importSummaries = new ArrayCollection();
        $this->disableCreditCard = false;
        $this->depositAccounts = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getOrderAlgorithm()
    {
        return $this->orderAlgorithm;
    }

    /**
     * @param string $orderAlgorithm
     */
    public function setOrderAlgorithm($orderAlgorithm)
    {
        if (!OrderAlgorithmType::isValid($orderAlgorithm)) {
            OrderAlgorithmType::throwsInvalid($orderAlgorithm);
        }

        $this->orderAlgorithm = $orderAlgorithm;
    }

    /**
     * @return string
     */
    public function getMailingAddressName()
    {
        return $this->mailingAddressName;
    }

    /**
     * @param string $mailingAddressName
     */
    public function setMailingAddressName($mailingAddressName)
    {
        $this->mailingAddressName = $mailingAddressName;
    }

    /**
     * @return ArrayCollection
     */
    public function getImportSummaries()
    {
        return $this->importSummaries;
    }

    /**
     * @param ImportSummary $import
     */
    public function addImportSummary(ImportSummary $import)
    {
        $this->importSummaries->add($import);
    }

    /**
     * @param ImportSummary $import
     */
    public function removeImportSummary(ImportSummary $import)
    {
        $this->importSummaries->remove($import);
    }

    /**
     * @param GroupSettings $groupSettings
     */
    public function setGroupSettings($groupSettings)
    {
        $this->groupSettings = $groupSettings;
    }

    /**
     * @return GroupSettings
     */
    public function getGroupSettings()
    {
        return $this->groupSettings;
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
     * @param  string $type
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
     * @param  string $name
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
     * Add lead
     *
     * @param  \CreditJeeves\DataBundle\Entity\Lead $lead
     * @return Group
     */
    public function addLead(\CreditJeeves\DataBundle\Entity\Lead $lead)
    {
        $this->leads[] = $lead;

        return $this;
    }

    /**
     * Remove lead
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $lead
     */
    public function removeLead(\CreditJeeves\DataBundle\Entity\Lead $lead)
    {
        $this->leads->removeElement($lead);
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
     * @param  \CreditJeeves\DataBundle\Entity\GroupIncentive $incentive
     * @return Group
     */
    public function addGroupIncentive(\CreditJeeves\DataBundle\Entity\GroupIncentive $incentive)
    {
        $this->incentives[] = $incentive;

        return $this;
    }

    /**
     * Remove incentive
     *
     * @param \CreditJeeves\DataBundle\Entity\GroupIncentive $incentive
     */
    public function removeGroupIncentive(\CreditJeeves\DataBundle\Entity\GroupIncentive $incentive)
    {
        $this->incentives->removeElement($incentive);
    }

    /**
     * Get incentives
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupIncentives()
    {
        return $this->incentives;
    }

    /**
     * Add dealer
     *
     * @param  \CreditJeeves\DataBundle\Entity\User $dealer
     * @return Group
     */
    public function addGroupDealer(\CreditJeeves\DataBundle\Entity\User $dealer)
    {
        $this->group_dealers[] = $dealer;

        return $this;
    }

    /**
     * Remove dealer
     *
     * @param \CreditJeeves\DataBundle\Entity\User $dealer
     */
    public function removeGroupDealer(\CreditJeeves\DataBundle\Entity\User $dealer)
    {
        $this->group_dealers->removeElement($dealer);
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
     * @param  string $websiteUrl
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
     * @param  string $description
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
     * @param  string $phone
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
     * @param  string $logoUrl
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
     * @param  string $fax
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
     * @param  string $streetAddress1
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
     * @param  string $streetAddress2
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
     * @param  string $city
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
     * @param  string $state
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
     * @param  string $zip
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

    /**
     *
     * @param  string $type
     * @return Group
     */
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
     * @param  string $code
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
     * @param  integer $targetScore
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

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * Set createdAt
     *
     * @param  \DateTime $createdAt
     * @return Group
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updatedAt
     *
     * @param  \DateTime $updatedAt
     * @return Group
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Add property
     *
     * @param  \RentJeeves\DataBundle\Entity\Property $property
     * @return Group
     */
    public function addGroupProperty(\RentJeeves\DataBundle\Entity\Property $property)
    {
        $hasProperty = false;
        foreach ($this->group_properties as $groupProperty) {
            if ($groupProperty == $property) {
                $hasProperty = true;
            }
        }

        if (!$hasProperty) {
            $this->group_properties[] = $property;
        }

        return $this;
    }

    /**
     * Remove property
     *
     * @param \RentJeeves\DataBundle\Entity\Property $property
     */
    public function removeGroupProperty(\RentJeeves\DataBundle\Entity\Property $property)
    {
        $this->group_properties->removeElement($property);
    }

    /**
     * Get properties
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupProperties()
    {
        return $this->group_properties;
    }

    /**
     * Set affiliate
     *
     * @param  \CreditJeeves\DataBundle\Entity\Affiliate $affiliate
     * @return Group
     */
    public function setAffiliate(\CreditJeeves\DataBundle\Entity\Affiliate $affiliate = null)
    {
        $this->affiliate = $affiliate;

        return $this;
    }

    /**
     * Get affiliate
     *
     * @return \CreditJeeves\DataBundle\Entity\Affiliate
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * Set contract_date
     *
     * @param  \DateTime $contractDate
     * @return Group
     */
    public function setContractDate($contractDate)
    {
        $this->contract_date = $contractDate;

        return $this;
    }

    /**
     * Get contract_date
     *
     * @return \DateTime
     */
    public function getContractDate()
    {
        return $this->contract_date;
    }

    /**
     * Set contract
     *
     * @param  string $contract
     * @return Group
     */
    public function setContract($contract)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return string
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Add unit
     *
     * @param  \RentJeeves\DataBundle\Entity\Unit $unit
     * @return Group
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
     * @param  \RentJeeves\DataBundle\Entity\Contract $contract
     * @return Group
     */
    public function addContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove Contract
     *
     * @param Contract
     */
    public function removeContract(\RentJeeves\DataBundle\Entity\Contract $contract)
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
     * @param $account
     */
    public function addDepositAccount($account)
    {
        $this->depositAccounts->add($account);
    }

    /**
     * @return DepositAccount|null
     */
    public function getRentDepositAccountForCurrentPaymentProcessor()
    {
        /** @var DepositAccount $depositAccount */
        foreach ($this->depositAccounts as $depositAccount) {
            if ($depositAccount->getType() === DepositAccountType::RENT &&
                $depositAccount->getPaymentProcessor() === $this->getGroupSettings()->getPaymentProcessor()
            ) {
                return $depositAccount;
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @param string $paymentProcessor
     *
     * @return null|DepositAccount
     */
    public function getDepositAccount($type, $paymentProcessor)
    {
        if (false === DepositAccountType::isValid($type)) {
            throw new \LogicException(sprintf('%s is not valid DepositAccountType', $type));
        }
        if (false === PaymentProcessor::isValid($paymentProcessor)) {
            throw new \LogicException(sprintf('%s is not valid PaymentProcessor', $paymentProcessor));
        }
        /** @var DepositAccount $depositAccount */
        foreach ($this->depositAccounts as $depositAccount) {
            if ($depositAccount->getType() === $type && $depositAccount->getPaymentProcessor() === $paymentProcessor) {
                return $depositAccount;
            }
        }

        return null;
    }

    /**
     * @return ArrayCollection|DepositAccount[]
     */
    public function getDepositAccounts()
    {
        return $this->depositAccounts;
    }

    /**
     * @param  BillingAccount $billingAccount
     * @return $this
     */
    public function addBillingAccount(BillingAccount $billingAccount)
    {
        $this->billingAccounts[] = $billingAccount;

        return $this;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\BillingAccount $billingAccount
     */
    public function removeBillingAccount(BillingAccount $billingAccount)
    {
        $this->billingAccounts->removeElement($billingAccount);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|BillingAccount[]
     */
    public function getBillingAccounts()
    {
        return $this->billingAccounts;
    }

    /**
     *
     * @param  \CreditJeeves\DataBundle\Entity\Dealer $dealer
     * @return \CreditJeeves\DataBundle\Model\Group
     */
    public function setDealer(\CreditJeeves\DataBundle\Entity\Dealer $dealer = null)
    {
        $this->dealers = $dealer;

        return $this;
    }

    /**
     *
     */
    public function getDealer()
    {
        return $this->dealers;
    }

    /**
     * Add phone number for the group
     *
     * @param  \RentJeeves\DataBundle\Entity\GroupPhone $phone
     * @return Group
     */
    public function addGroupPhone(\RentJeeves\DataBundle\Entity\GroupPhone $phone)
    {
        $this->groupPhones[] = $phone;

        return $this;
    }

    /**
     * Remove Group phone
     *
     * @param \RentJeeves\DataBundle\Entity\GroupPhone $phone
     */
    public function removeGroupPhone(\RentJeeves\DataBundle\Entity\GroupPhone $phone)
    {
        $this->groupPhones->removeElement($phone);
    }

    /**
     * Get group phones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupPhones()
    {
        return $this->groupPhones;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Landlord[]
     */
    public function getGroupAgents()
    {
        return $this->group_agents;
    }

    /**
     * @param ContractWaiting $waitingContract
     */
    public function addWaitingContract(ContractWaiting $waitingContract)
    {
        $this->waitingContracts[] = $waitingContract;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWaitingContracts()
    {
        return $this->waitingContracts;
    }

    /**
     * @return string
     */
    public function getStatementDescriptor()
    {
        return $this->statementDescriptor;
    }

    /**
     * @param string $statementDescriptor
     */
    public function setStatementDescriptor($statementDescriptor)
    {
        $this->statementDescriptor = $statementDescriptor;
    }

    /**
     * @return GroupAccountNumberMapping
     */
    public function getAccountNumberMapping()
    {
        return $this->accountNumberMapping;
    }

    /**
     * @param GroupAccountNumberMapping $accountNumberMapping
     */
    public function setAccountNumberMapping(GroupAccountNumberMapping $accountNumberMapping)
    {
        $this->accountNumberMapping = $accountNumberMapping;
    }

    /**
     * @return mixed
     */
    public function isDisableCreditCard()
    {
        return $this->disableCreditCard;
    }

    /**
     * @param mixed $disableCreditCard
     */
    public function setDisableCreditCard($disableCreditCard)
    {
        $this->disableCreditCard = $disableCreditCard;
    }

    /**
     * @return AciCollectPayGroupProfile
     */
    public function getAciCollectPayProfile()
    {
        return $this->aciCollectPayProfile;
    }

    /**
     * @param AciCollectPayGroupProfile $aciCollectPayProfile
     */
    public function setAciCollectPayProfile($aciCollectPayProfile)
    {
        $this->aciCollectPayProfile = $aciCollectPayProfile;
    }

    /**
     * @return AciImportProfileMap
     */
    public function getAciImportProfileMap()
    {
        return $this->aciImportProfileMap;
    }

    /**
     * @param AciImportProfileMap $aciImportProfileMap
     */
    public function setAciImportProfileMap(AciImportProfileMap $aciImportProfileMap)
    {
        $this->aciImportProfileMap = $aciImportProfileMap;
    }

    /**
     * @return string
     */
    public function getExternalGroupId()
    {
        return $this->externalGroupId;
    }

    /**
     * @param string $externalGroupId
     */
    public function setExternalGroupId($externalGroupId)
    {
        $this->externalGroupId = $externalGroupId;
    }

    /**
     * @Assert\True(message = "error.group.required_fields_for_paydirect", groups={"holding"})
     */
    public function isMailingAddressNotEmptyForPayDirect()
    {
        if ($this->getOrderAlgorithm() === OrderAlgorithmType::PAYDIRECT &&
            (empty($this->mailingAddressName) || empty($this->city) || empty($this->state) || empty($this->zip) ||
                empty($this->street_address_1)
            )
        ) {
            return false;
        }

        return true;
    }
}
