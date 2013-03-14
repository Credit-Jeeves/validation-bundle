<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Arf\ArfReport;

class ReportSummaryController extends Controller
{
    public function indexAction()
    {
        $cjUser    = $this->getUser();
        $Report    = $cjUser->getReportsPrequal()->last();
        $sDate     = $Report->getCreatedAt()->format('M j, Y');
        $aCreditSummary = $Report->getCreditSummary();
        return $this->render(
            'ComponentBundle:ReportSummary:index.html.twig',
            array(
                'sDate' => $sDate,
                'aCreditSummary' => $aCreditSummary,
                )
            );
    }
}
