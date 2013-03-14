<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Arf\ArfReport;

class CreditSummaryController extends Controller
{
    public function indexAction()
    {
        $cjUser    = $this->getUser();
        $Report    = $cjUser->getReportsPrequal()->last();
        $sDate     = $Report->getCreatedAt()->format('M j, Y');
        $aCreditSummary = $Report->getCreditSummary();
        $aCreditSummary['collections'] = $Report->getCountTradelineCollections();
        return $this->render(
            'ComponentBundle:CreditSummary:index.html.twig',
            array(
                'sDate' => $sDate,
                'aCreditSummary' => $aCreditSummary,
                )
            );
    }
}
