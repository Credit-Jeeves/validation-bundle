<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\DisputeCode;
use LogicException;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\ContractStatus;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CoreBundle\DateTime;

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
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
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
     */
    protected $holding;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="contracts",
     *     fetch="EAGER"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="contracts",
     *     cascade={"persist"}
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
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
     */
    protected $property;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     inversedBy="contracts",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="unit_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
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
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
     */
    protected $status;

    /**
     * @ORM\Column(
     *     type="PaymentAccepted",
     *     nullable=false,
     *     name="payment_accepted",
     *     options={
     *         "default"="0"
     *     }
     * )
     * @Gedmo\Versioned
     */
    protected $paymentAccepted = PaymentAccepted::ANY;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     nullable=false,
     *     name="payment_allowed",
     *     options={
     *         "default"="1"
     *     }
     * )
     * @Gedmo\Versioned
     */
    protected $paymentAllowed = true;

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
     *     pattern = "/^\d+(\.\d{1,2})?$/",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
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
     *     name="integrated_balance",
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
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
     * @Gedmo\Versioned
     *
     */
    protected $integratedBalance = 0.00;

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
     *     name="report_to_experian",
     *     type="boolean",
     *     nullable=true,
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $reportToExperian = 0;

    /**
     * @ORM\Column(
     *     name="report_to_trans_union",
     *     type="boolean",
     *     nullable=true,
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $reportToTransUnion = 0;

    /**
     * @ORM\Column(
     *     name="report_to_equifax",
     *     type="boolean",
     *     nullable=true,
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $reportToEquifax = 0;

    /**
     * @ORM\Column(
     *     name="experian_start_at",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $experianStartAt;

    /**
     * @ORM\Column(
     *     name="trans_union_start_at",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $transUnionStartAt;

    /**
     * @ORM\Column(
     *     name="equifax_start_at",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $equifaxStartAt;

    /**
     * @ORM\Column(name="due_date", type="integer", nullable=true)
     * @Serializer\SerializedName("dueDate")
     *
     * @var int
     */
    protected $dueDate;

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
     *         "tenant_invite"
     *     }
     * )
     * @Serializer\SerializedName("finishAt")
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
     * @Gedmo\Versioned
     */
    protected $finishAt = null;

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
     * @Gedmo\Versioned
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="contract",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     * @var ArrayCollection
     */
    protected $operations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Payment",
     *     mappedBy="contract",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
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
     * @var ArrayCollection
     */
    protected $histories;

    /**
     * @ORM\Column(
     *     type="DisputeCode",
     *     nullable=true,
     *     options={
     *         "default"="BLANK"
     *     }
     * )
     */
    protected $disputeCode = DisputeCode::DISPUTE_CODE_BLANK;

    /**
     * @ORM\Column(
     *     name="external_lease_id",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $externalLeaseId;

    /**
     * @var ArrayCollection|ProfitStarsRegisteredContract[]
     *
     * @ORM\OneToMany(
     *      targetEntity="RentJeeves\DataBundle\Entity\ProfitStarsRegisteredContract",
     *      mappedBy="contract"
     * )
     */
    protected $profitStarsRegisteredContracts;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->histories = new ArrayCollection();
        $this->profitStarsRegisteredContracts = new ArrayCollection();
    }

    /**
     * @return \DateTime|null
     */
    public function getEquifaxStartAt()
    {
        return $this->equifaxStartAt;
    }

    /**
     * @param \DateTime|null $equifaxStartAt
     */
    public function setEquifaxStartAt($equifaxStartAt)
    {
        $this->equifaxStartAt = $equifaxStartAt;
    }

    /**
     * @return boolean
     */
    public function getReportToEquifax()
    {
        return $this->reportToEquifax;
    }

    /**
     * @param boolean $reportToEquifax
     */
    public function setReportToEquifax($reportToEquifax)
    {
        $this->reportToEquifax = $reportToEquifax;
    }

    /**
     * @return string
     */
    public function getExternalLeaseId()
    {
        return $this->externalLeaseId;
    }

    /**
     * @param string $externalLeaseId
     */
    public function setExternalLeaseId($externalLeaseId)
    {
        $this->externalLeaseId = $externalLeaseId;
    }

    /**
     * @return integer
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param integer $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
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
     * @param  \RentJeeves\DataBundle\Entity\Tenant $tenant
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
     * @param  Holding  $holding
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
     * @param  Holding  $holding
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
     * @param  Property|null $property
     * @return Contract
     */
    public function setProperty(Property $property = null)
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
     * @param  Unit     $unit
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
     * @param  string   $search
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
     * @param  string $status
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
     * @param  double $rent
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
     * @param float $integratedBalance
     */
    public function setIntegratedBalance($integratedBalance)
    {
        $this->integratedBalance = $integratedBalance;
    }

    /**
     * @return float
     */
    public function getIntegratedBalance()
    {
        return $this->integratedBalance;
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
     * @param  DateTime $paidTo
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
     * @return DateTime
     */
    public function getPaidTo()
    {
        return $this->paidTo;
    }

    /**
     * Set dueDate
     *
     * @param  integer         $dueDate
     * @throws \LogicException
     * @return $this
     */
    public function setDueDate($dueDate)
    {
        $dueDate = (int) $dueDate;
        if ($dueDate > 31 || $dueDate < 1) {
            throw new LogicException("Due date can't be more than 31 and less than 1");
        }
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return integer
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    public static function getRangeDueDate()
    {
        $dueDate = array();
        foreach (range(1, 31, 1) as $value) {
            $dueDate[$value] = $value;
        }

        return $dueDate;
    }

    /**
     * Set startAt
     *
     * @param  DateTime $startAt
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
     * @return DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set finishAt
     *
     * @param  DateTime $finishAt
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
     * @return DateTime
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * Set createdAt
     *
     * @param  DateTime $createdAt
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
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param  DateTime $updatedAt
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
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add payment
     *
     * @param  \CreditJeeves\DataBundle\Entity\Operation $operation
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
     * @param  \RentJeeves\DataBundle\Entity\Payment $payment
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

    /**
     * @param DateTime $experianStartAt
     */
    public function setExperianStartAt($experianStartAt)
    {
        $this->experianStartAt = $experianStartAt;
    }

    /**
     * @return DateTime
     */
    public function getExperianStartAt()
    {
        return $this->experianStartAt;
    }

    /**
     * @param boolean $reportExperian
     */
    public function setReportToExperian($reportExperian)
    {
        $this->reportToExperian = $reportExperian;
    }

    /**
     * @return boolean
     */
    public function getReportToExperian()
    {
        return $this->reportToExperian;
    }

    /**
     * @param boolean $reportTransUnion
     */
    public function setReportToTransUnion($reportTransUnion)
    {
        $this->reportToTransUnion = $reportTransUnion;
    }

    /**
     * @return boolean
     */
    public function getReportToTransUnion()
    {
        return $this->reportToTransUnion;
    }

    /**
     * @param DateTime $transUnionStartAt
     */
    public function setTransUnionStartAt($transUnionStartAt)
    {
        $this->transUnionStartAt = $transUnionStartAt;
    }

    /**
     * @return DateTime
     */
    public function getTransUnionStartAt()
    {
        return $this->transUnionStartAt;
    }

    /**
     * @param boolean $paymentAllowed
     */
    public function setPaymentAllowed($paymentAllowed)
    {
        $this->paymentAllowed = (bool) $paymentAllowed;
    }

    /**
     * @return boolean
     */
    public function isPaymentAllowed()
    {
        return $this->paymentAllowed;
    }

    /**
     * @return ArrayCollection|ProfitStarsRegisteredContract[]
     */
    public function getProfitStarsRegisteredContracts()
    {
        return $this->profitStarsRegisteredContracts;
    }

    /**
     * @param ProfitStarsRegisteredContract $profitStarsRegisteredContract
     */
    public function addProfitStarsRegisteredContract(ProfitStarsRegisteredContract $profitStarsRegisteredContract)
    {
        $this->profitStarsRegisteredContracts->add($profitStarsRegisteredContract);
    }
}
