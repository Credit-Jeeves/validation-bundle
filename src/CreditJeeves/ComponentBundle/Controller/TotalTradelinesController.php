<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TotalTradelinesController extends Controller
{
    public function indexAction()
    {
        $nOpened = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last()->getCountApplicantOpenedTradelines();
        $nClosed = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last()->getCountApplicantClosedTradelines();
        $nTotal = $nOpened + $nClosed;

        return $this->render(
            'ComponentBundle:TotalTradelines:index.html.twig',
            array(
                'nTotal' => $nTotal,
                'nOpened' => $nOpened,
                'nClosed' => $nClosed,
                )
        );
    }
}
