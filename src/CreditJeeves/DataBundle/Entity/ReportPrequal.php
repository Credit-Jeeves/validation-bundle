<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\UserBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class ReportPrequal extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\UserBundle\Entity\User", inversedBy="reportsPrequal")
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
     * @param \CreditJeeves\UserBundle\Entity\User $user
     * @return ReportPrequal
     */
    public function setUser(\CreditJeeves\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get userPostFlush
     *
     * @return \CreditJeeves\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @ORM\PostPersist
     */
    public function postPersist(/*PostFlushEventArgs $args*/) {
//        $arfReport = $this->getArfReport();
//        $newScore = $arfReport->getValue(ArfParser::SEGMENT_RISK_MODEL, ArfParser::REPORT_SCORE);
//        $score = new Score();
//        $score->setUser($this->getUser());
//        $score->setScore($newScore);
//        $args->getEntityManager()->persist($score);
    }
}
