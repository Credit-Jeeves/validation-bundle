<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use DateTime;

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
     * @ORM\Column(
     *     type="integer",
     *     nullable=false
     * )
     */
    protected $amount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(
     *     name="cj_applicant_report_id",
     *     type="bigint", nullable=true
     * )
     */
    protected $cjApplicantReportId;

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
     *     referencedColumnName="id"
     * )
     */
    protected $order;

    /**
     * @var \CreditJeeves\DataBundle\Entity\ReportD2c
     *
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\ReportD2c",
     *     inversedBy="operation"
     * )
     *
     * @ORM\JoinColumn(
     *     name="cj_applicant_report_id",
     *     referencedColumnName="id"
     * )
     */
    protected $reportD2c;

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
     * Set cjApplicantReportId
     *
     * @param integer $cjApplicantReportId
     * @return Operation
     */
    public function setCjApplicantReportId($cjApplicantReportId)
    {
        $this->cjApplicantReportId = $cjApplicantReportId;
        return $this;
    }

    /**
     * Get cjApplicantReportId
     *
     * @return integer 
     */
    public function getCjApplicantReportId()
    {
        return $this->cjApplicantReportId;
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

    /**
     * Add reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportD2c
     * 
     * @return Operation
     */
    public function setReportD2c(\CreditJeeves\DataBundle\Entity\ReportD2c $reportD2c)
    {
        $this->reportD2c = $reportD2c;

        return $this;
    }

    /**
     * Get reportsD2c
     *
     * @return \CreditJeeves\DataBundle\Entity\ReportD2c
     */
    public function getReportD2c()
    {
        return $this->reportD2c;
    }

    /**
     * Set order
     *
     * @param \CreditJeeves\DataBundle\Entity\Order $orders
     *
     * @return Operation
     */
    public function setOrder(\CreditJeeves\DataBundle\Entity\Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \CreditJeeves\DataBundle\Entity\Order
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
