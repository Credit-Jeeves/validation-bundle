<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SummaryController extends Controller
{
    /**
     * @Route("/summary", name="applicant_summary")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $cjUser = $this->get('core.session.applicant')->getUser();
        $sEmail = $cjUser->getEmail();
        $Report  = $cjUser->getReportsPrequal()->last();
        return array(
            'sEmail' => $sEmail,
            'Report' => $Report,
        );
    }
}
