<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SummaryController extends Controller
{
    /**
     * @Route("/summary", name="tenant_summary")
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $sEmail = $user->getEmail();
        $Report  = $this->getReport();
        if (!$Report) {
            return new RedirectResponse($this->get('router')->generate('core_report_get'));
        }
//         echo '<pre>';
//         print_r($Report->getArfArray());
        //exit;
        $Score = $this->getScore();
        return array(
            'sEmail' => $sEmail,
            'Report' => $Report,
            'Score' => $Score,
            'User' => $user,
        );
    }
}
