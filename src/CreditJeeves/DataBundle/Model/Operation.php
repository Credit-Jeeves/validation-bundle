<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class Operation
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
    protected $type;

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
}
