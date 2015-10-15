<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Report as BaseReport;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="ReportType")
 * @ORM\DiscriminatorMap({
 *     "prequal" = "ReportPrequal",
 *     "d2c" = "ReportD2c",
 *     "tu_snapshot" = "ReportTransunionSnapshot"
 * })
 * @ORM\Table(name="cj_applicant_report")
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Report extends BaseReport implements ReportSummaryInterface
{
    /**
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="report",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $operation;
}
