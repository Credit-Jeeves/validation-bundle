<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Operation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="OperationType")
     */
    protected $type = OperationType::REPORT;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_applicant_report_id", type="integer")
     */
    protected $cjApplicantReportId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\CreditJeeves\DataBundle\Entity\Order", mappedBy="operations")
     */
    protected $orders;

//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(
//     *     targetEntity="OrderOperation",
//     *     mappedBy="operation",
//     *     cascade={"persist", "remove", "merge"},
//     *     orphanRemoval=true
//     * )
//     */
//    protected $orderOperations;

    /**
     * @var \CreditJeeves\DataBundle\Entity\ReportD2c
     *
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\ReportD2c",
     *     inversedBy="operation"
     * )
     *
     * @ORM\JoinColumn(name="cj_applicant_report_id", referencedColumnName="id")
     */
    protected $reportD2c;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->orders = new ArrayCollection();
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
     * @param \DateTime $createdAt
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
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Add reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportD2c
     * @return User
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
}
