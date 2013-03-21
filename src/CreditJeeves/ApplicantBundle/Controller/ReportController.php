<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ReportController extends Controller
{
    /**
     * @Route("/report", name="applicant_report")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $Report = $this->getUser()->getReportsD2c()->last();
        $sEmail = $this->getUser()->getEmail();
        return array(
            'sEmail' => $sEmail,
            'Report' => $Report,
            );
    }
}
