<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\ContractStatus;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Contract
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Tenant",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="tenant_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\Exclude
     */
    protected $tenant;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\Exclude
     */
    protected $holding;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\SerializedName("groupId")
     * @Serializer\Accessor(getter="getGroupId")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="property_id",
     *     referencedColumnName="id"
     * )
     * @Assert\NotBlank(
     *     message="error.property.empty",
     *     groups={
     *         "tenant_invite"
     *     }
     * )
     */
    protected $property;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     inversedBy="contracts"
     * )
     * @ORM\JoinColumn(
     *     name="unit_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $unit;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @Assert\Length(
     *     max=15,
     *     maxMessage="unit.name.long",
     *     groups={
     *         "tenant_contract",
     *     }
     * )
     * @Serializer\Exclude
     */
    protected $search;

    /**
     * @ORM\Column(
     *     type="ContractStatus",
     *     options={
     *         "default"="pending"
     *     }
     * )
     * @Gedmo\Versioned
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $status;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.rent.empty",
     *     groups={
     *         "tenant_invite",
     *         "import"
     *     }
     * )
     * @Gedmo\Versioned
     * @Assert\Regex(
     *     pattern = "/^-?\d+(\.\d{1,2})?$/",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $rent = null;


    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=true,
     *     name="uncollected_balance"
     * )
     * @Gedmo\Versioned
     */
    protected $uncollectedBalance;


    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=false,
     *     name="balance",
     *     options={
     *          "default":"0.00"
     *     }
     * )
     * @Gedmo\Versioned
     */
    protected $balance = 0.00;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=false,
     *     name="imported_balance",
     *     options={
     *          "default":"0.00"
     *     }
     * )
     * @Assert\NotBlank(
     *     message="error.balance.empty",
     *     groups={
     *         "import"
     *     }
     * )
     * @Assert\Regex(
     *     pattern = "/^-?\d+(\.\d{1,2})?$/",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     * @Gedmo\Versioned
     */
    protected $importedBalance = 0.00;

    /**
     * @ORM\Column(
     *     name="paid_to",
     *     type="date",
     *     nullable=true
     * )
     * @Serializer\SerializedName("paidTo")
     * @Serializer\Groups({"RentJeevesImport"})
     * @Gedmo\Versioned
     */
    protected $paidTo;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     nullable=true,
     *     options={
     *         "default"="0"
     *     }
     * )
     * @Gedmo\Versioned
     */
    protected $reporting = 0;

    /**
     * @ORM\Column(
     *     name="start_at",
     *     type="date",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.start.empty",
     *     groups={
     *         "tenant_invite",
     *         "import"
     *     }
     * )
     * @Serializer\SerializedName("startAt")
     * @Serializer\Groups({"RentJeevesImport"})
     * @Gedmo\Versioned
     */
    protected $startAt;

    /**
     * @ORM\Column(
     *     name="finish_at",
     *     type="date",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.finish.empty",
     *     groups={
     *         "tenant_invite",
     *         "import"
     *     }
     * )
     * @Serializer\SerializedName("finishAt")
     * @Serializer\Groups({"RentJeevesImport"})
     * @Gedmo\Versioned
     */
    protected $finishAt;
    

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
     * @Gedmo\Versioned
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="contract",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @Serializer\Exclude
     * @var ArrayCollection
     */
    protected $operations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Payment",
     *     mappedBy="contract",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true,
     *     fetch = "EAGER"
     * )
     * @Serializer\Exclude
     * @var ArrayCollection
     */
    protected $payments;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ContractHistory",
     *     mappedBy="object",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $histories;

    /**
     * @ORM\Column(
     *     type="DisputeCode",
     *     nullable=true,
     *     options={
     *         "default"=" "
     *     }
     * )
     */
    protected $disputeCode;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->histories = new ArrayCollection();
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
     * Set Tenant
     *
     * @param \RentJeeves\DataBundle\Entity\Tenant $tenant
     * @return contract
     */
    public function setTenant(\RentJeeves\DataBundle\Entity\Tenant $tenant)
    {
        $this->tenant = $tenant;
        return $this;
    }

    /**
     * Get Tenant
     *
     * @return \RentJeeves\DataBundle\Entity\Tenant
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Set Holding
     *
     * @param Holding $holding
     * @return Contract
     */
    public function setHolding(\CreditJeeves\DataBundle\Entity\Holding $holding)
    {
        $this->holding = $holding;
        return $this;
    }

    /**
     * Get Holding
     *
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * Set Group
     *
     * @param Holding $holding
     * @return Contract
     */
    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get Group
     *
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set Property
     *
     * @param Property $property
     * @return Contract
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Get Property
     *
     * @return \RentJeeves\DataBundle\Entity\Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set Unit
     *
     * @param Unit $unit
     * @return Contract
     */
    public function setUnit(Unit $unit = null)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * Get Unit
     *
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set search
     *
     * @param string $search
     * @return Contract
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Get search
     *
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Unit
     */
    public function setStatus($status = ContractStatus::PENDING)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set rent
     *
     * @param double $rent
     * @return Unit
     */
    public function setRent($rent)
    {
        $this->rent = $rent;
        return $this;
    }

    /**
     * Get rent
     *
     * @return double
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param float $importedBalance
     */
    public function setImportedBalance($importedBalance)
    {
        $this->importedBalance = $importedBalance;
    }

    /**
     * @return float
     */
    public function getImportedBalance()
    {
        return $this->importedBalance;
    }


    /**
     * @param float $uncollectedBalance
     */
    public function setUncollectedBalance($uncollectedBalance)
    {
        $this->uncollectedBalance = $uncollectedBalance;
    }

    /**
     * @return float
     */
    public function getUncollectedBalance()
    {
        return $this->uncollectedBalance;
    }

    /**
     * Set Paid to
     *
     * @param \DateTime $paidTo
     * @return Contract
     */
    public function setPaidTo($paidTo)
    {
        $this->paidTo = $paidTo;
        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getPaidTo()
    {
        $date = $this->paidTo;
        if (empty($date)) {
            $date = $this->getStartAt();
        }
        return $date;
    }

    /**
     * Set Reporting
     *
     * @param boolean $reporting
     * @return Contract
     */
    public function setReporting($reporting)
    {
        $this->reporting = $reporting;
        return $this;
    }

    /**
     * Get Reporting
     *
     * @return boolean
     */
    public function getReporting()
    {
        return $this->reporting;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     * @return Contract
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set finishAt
     *
     * @param \DateTime $finishAt
     * @return Contract
     */
    public function setFinishAt($finishAt)
    {
        $this->finishAt = $finishAt;
        return $this;
    }

    /**
     * Get finishAt
     *
     * @return \DateTime
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Contract
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
     * @return Contract
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
     * Add payment
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $operation
     * @return Contract
     */
    public function addOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations[] = $operation;
        return $this;
    }

    /**
     * Remove payment
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $operation
     */
    public function removeOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations->removeElement($operation);
    }

    /**
     * Get operations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Add payment
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $payment
     * @return Contract
     */
    public function addPayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;
        return $this;
    }

    /**
     * Remove payment
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $opeartion
     */
    public function removePayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $disputeCode
     */
    public function setDisputeCode($disputeCode)
    {
        $this->disputeCode = $disputeCode;
    }

    /**
     * @return mixed
     */
    public function getDisputeCode()
    {
        return $this->disputeCode;
    }
}
