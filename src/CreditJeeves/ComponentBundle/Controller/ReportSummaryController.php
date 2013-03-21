<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ReportSummaryController extends Controller
{
    /**
     * @Template()
     * @param \Report $Report
     */
    public function indexAction(Report $Report)
    {
        $sDate     = $Report->getCreatedAt()->format('M j, Y');
        $aCreditSummary = $Report->getCreditSummary();
        return array(
                'sDate' => $sDate,
                'aCreditSummary' => $aCreditSummary,
            );
    }
}
