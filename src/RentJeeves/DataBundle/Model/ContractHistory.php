<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\ContractStatus;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use DateTime;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class ContractHistory extends AbstractLogEntry
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="editor_id", type="bigint", nullable=true)
     */
    protected $editorId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="bigint", nullable=true)
     */
    protected $objectId;

    /**
     * @ORM\ManyToOne(targetEntity="RentJeeves\DataBundle\Entity\Contract", inversedBy="histories")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    protected $object;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
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
     */
    protected $status;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=true
     * )
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
     */
    protected $importedBalance = 0.00;

    /**
     * @ORM\Column(
     *     name="paid_to",
     *     type="date",
     *     nullable=true
     * )
     * @Serializer\SerializedName("paidTo")
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
     */
    protected $reporting = 0;

    /**
     * @ORM\Column(
     *     name="start_at",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $startAt;

    /**
     * @ORM\Column(
     *     name="finish_at",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $finishAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     */
    protected $updatedAt;

    /**
     * @var string $loggedAt
     *
     * @~ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @var string $objectClass
     *
     * @~ORM\Column(name="object_class", type="string", length=255)
     */
    protected $objectClass;

    /**
     * @var integer $version
     *
     * @~ORM\Column(type="bigint")
     */
    protected $version;

    /**
     * @var string $data
     *
     * @~ORM\Column(type="array", nullable=true)
     */
    protected $data;

    /**
     * @var string $data
     *
     * @~ORM\Column(length=255, nullable=true)
     */
    protected $username;

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

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->payments = new ArrayCollection();
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
     * Set editorId
     *
     * @param integer $editorId
     * @return $this
     */
    public function setEditorId($editorId)
    {
        $this->editorId = $editorId;

        return $this;
    }

    /**
     * Get editorId
     *
     * @return integer
     */
    public function getEditorId()
    {
        return $this->editorId;
    }

    /**
     * @param $object
     *
     * @return $this
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Contract
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set Tenant
     *
     * @param \RentJeeves\DataBundle\Entity\Tenant $tenant
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @param DateTime $paidTo
     * @return $this
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
     * @return $this
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
     * @param DateTime $startAt
     * @return $this
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
     * @param DateTime $finishAt
     * @return $this
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
     * Set updatedAt
     *
     * @param DateTime $updatedAt
     * @return $this
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
     * @param \CreditJeeves\DataBundle\Entity\Operation $operation
     * @return $this
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
     * @return $this
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
}
