<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\ApplicantController as Controller;
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
