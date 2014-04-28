<?php
namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\CoreBundle\Enum\ScoreModelType;
use CreditJeeves\DataBundle\EventListener\DoctrineListener as Base;
use CreditJeeves\DataBundle\Entity\Report;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class DoctrineListener extends Base
{
    /**
     * @param Report $report
     *
     * @return int
     */
    protected function getReportScore($report)
    {
        return $report->getArfReport()->getScore(ScoreModelType::VANTAGE3);
    }
}
