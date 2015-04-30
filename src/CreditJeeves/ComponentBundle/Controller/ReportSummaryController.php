<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ReportSummaryController extends Controller
{
    /**
     *
     * This component currently only supports Experian prequal reports
     *
     * @Template()
     * @param \Report $Report
     */
    public function indexAction(Report $Report)
    {
        $dateShortFormat = $this->container->getParameter('date_short');
        $sDate     = $Report->getCreatedAt()->format($dateShortFormat);

        $aCreditSummary = $Report->getCreditSummary();
        return array(
                'sDate' => $sDate,
                'aCreditSummary' => $aCreditSummary,
            );
    }
}
