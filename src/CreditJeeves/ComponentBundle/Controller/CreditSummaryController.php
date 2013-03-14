<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CreditSummaryController extends Controller
{
    public function indexAction()
    {
        $cjUser    = $this->getUser();
        $Report    = $cjUser->getReportsPrequal()->last();
        $sDate     = $Report->getCreatedAt()->format('M j, Y');
        $aCreditSummary = $Report->getCreditSummary();
        $aCreditSummary['collections'] = $Report->getCountTradelineCollections();
        $aAutomotive = $Report->getAutomotiveSummary();
        $nAutomotive = isset($aAutomotive['total_open_monthly_payment']) ? $aAutomotive['total_open_monthly_payment'] : 0;

        return $this->render(
            'ComponentBundle:CreditSummary:index.html.twig',
            array(
                'sDate' => $sDate,
                'aCreditSummary' => $aCreditSummary,
                'nAutomotive' => $nAutomotive,
                )
            );
    }
}
