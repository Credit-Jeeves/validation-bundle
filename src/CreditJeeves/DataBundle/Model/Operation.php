<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Order as OrderEntity;
use CreditJeeves\DataBundle\Entity\Report as ReportEntity;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class Operation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="type",
     *     type="OperationType"
     * )
     */
    protected $type = OperationType::REPORT;

    /**
     * @var float
     *
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=false
     * )
     * @Assert\Regex(
     *     pattern = "/^\d+(\.\d{1,2})?$/",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Assert\NotBlank(
     *     groups={
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $amount = 0;

    /**
     * @ORM\Column(
     *     name="paid_for",
     *     type="date",
     *     nullable=false
     * )
     * @Assert\NotBlank(
     *     groups={
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     *
     * @var DateTime
     */
    protected $paidFor;

    /**
     * @var DateTime
     *
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     cascade={"persist", "remove", "merge"},
     *     inversedBy="operations",
     *     fetch = "EAGER"
     * )
     *
     * @ORM\JoinColumn(
     *     name="order_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $order;

    /**
     * @var Report
     *
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Report",
     *     inversedBy="operation"
     * )
     *
     * @ORM\JoinColumn(
     *     name="cj_applicant_report_id",
     *     referencedColumnName="id"
     * )
     */
    protected $report;

    /**
     * @var \RentJeeves\DataBundle\Entity\Contract
     *
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     inversedBy="operations",
     *     cascade={"all"}
     * )
     * @ORM\JoinColumn(
     *     name="contract_id",
     *     referencedColumnName="id"
     * )
     */
    protected $contract;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group"
     * )
     */
    protected $group;

    public function __construct()
    {
        $this->createdAt = new DateTime();
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
     * @param OperationType $type
     * @return Operation
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return OperationType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set amount
     *
     * @param double $amount
     * @return Operation
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return integer|null
     */
    public function getUserReportId()
    {
        return $this->report ? $this->report->getId() : null;
    }

    /**
     * Set paidFor
     *
     * @param DateTime $paidFor
     * @return Operation
     */
    public function setPaidFor($paidFor)
    {
        $this->paidFor = $paidFor;

        return $this;
    }

    /**
     * Get paidFor
     *
     * @return DateTime
     */
    public function getPaidFor()
    {
        return $this->paidFor;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return Operation
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

    public function setReport(ReportEntity $report)
    {
        $this->report = $report;
    }

    /**
     * @return ReportTransunionSnapshot
     */
    public function getReportTransunionSnapshot()
    {
        return ($this->report instanceof ReportTransunionSnapshot) ? $this->report : null;
    }

    /**
     * @return ReportPrequal
     */
    public function getReportPrequal()
    {
        return ($this->report instanceof ReportPrequal) ? $this->report : null;
    }

    /**
     * Set order
     *
     * @param OrderEntity $orders
     *
     * @return Operation
     */
    public function setOrder(OrderEntity $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return OrderEntity
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set Contract
     *
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     *
     * @return Operation
     */
    public function setContract(\RentJeeves\DataBundle\Entity\Contract $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get Contract
     *
     * @return \RentJeeves\DataBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     *
     * @return Operation
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
