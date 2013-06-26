<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $Score = $this->getScore();
        return array(
            'sEmail' => $sEmail,
            'Report' => $Report,
            'Score' => $Score,
        );
    }
}
