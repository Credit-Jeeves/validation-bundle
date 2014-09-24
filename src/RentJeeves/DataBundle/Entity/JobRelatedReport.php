<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Report;

/**
 * @ORM\Entity
 */
class JobRelatedReport extends JobRelatedEntities
{
    /**
     * @ORM\ManyToOne(
     *      targetEntity = "\CreditJeeves\DataBundle\Entity\Report",
     *      inversedBy = "jobs",
     *      fetch = "EAGER"
     * )
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", nullable=true)
     */
    protected $report;

    /**
     * @param Report $report
     *
     * @return $this
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }
}
