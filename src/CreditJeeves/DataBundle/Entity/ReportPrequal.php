<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\ReportPrequalRepository")
 */
class ReportPrequal extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="reportsPrequal")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var integer
     */
    protected $id;

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
     * Set cj_applicant_id
     *
     * @param integer $cjApplicantId
     * @return ReportPrequal
     */
    public function setCjApplicantId($cjApplicantId)
    {
        $this->cj_applicant_id = $cjApplicantId;
    
        return $this;
    }

    /**
     * Get cj_applicant_id
     *
     * @return integer 
     */
    public function getCjApplicantId()
    {
        return $this->cj_applicant_id;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return ReportPrequal
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
     * Set user
     *
     * @param \CreditJeeves\DataBundle\Entity\User $user
     * @return ReportPrequal
     */
    public function setUser(\CreditJeeves\DataBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

}