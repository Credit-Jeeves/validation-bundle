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
class Report extends BaseReport
{
}
