<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\SimulationBundle\Entity\Atb as BaseAtb;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\AtbRepository")
 * @ORM\Table(name="atb_simulation")
 * @ORM\HasLifecycleCallbacks()
 */
class Atb extends BaseAtb
{
    /**
     * @ORM\Column(type="bigint")
     */
    protected $cj_applicant_report_id;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\ReportPrequal", inversedBy="atbs", cascade={"persist"})
     * @ORM\JoinColumn(name="cj_applicant_report_id", referencedColumnName="id")
     */
    protected $report;

    /**
     * Set cj_applicant_id
     *
     * @param integer $cjApplicantId
     * @return Atb
     */
    public function setReportId($reportId)
    {
        $this->cj_applicant_id = $reportId;

        return $this;
    }

    /**
     * Get cj_applicant_id
     *
     * @return integer
     */
    public function getReportId()
    {
        return $this->cj_applicant_id;
    }

    /**
     * Set report
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $report
     * @return Atb
     */
    public function setReport(\CreditJeeves\DataBundle\Entity\ReportPrequal $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \CreditJeeves\DataBundle\Entity\ReportPrequal
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @ORM\PreRemove
     */
    public function methodPreRemove()
    {
    }

    /**
     * @ORM\PostRemove
     */
    public function methodPostRemove()
    {
    }

    /**
     * @ORM\PrePersist
     */
    public function methodPrePersist()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PostPersist
     */
    public function methodPostPersist()
    {
    }

    /**
     * @ORM\PreUpdate
     */
    public function methodPreUpdate()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PostUpdate
     */
    public function methodPostUpdate()
    {
    }

    /**
     * @ORM\PostLoad
     */
    public function methodPostLoad()
    {
    }
}
