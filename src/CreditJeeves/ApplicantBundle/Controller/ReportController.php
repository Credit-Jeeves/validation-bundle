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
        $Report = $this->get('core.session.applicant')->getUser()->getReportsD2c()->last();
        $sEmail = $this->get('core.session.applicant')->getUser()->getEmail();
        $sSupportEmail = $this->container->getParameter('support_email');
        $sSupportPhone = $this->container->getParameter('support_phone');
        return array(
            'sEmail' => $sEmail,
            'Report' => $Report,
            'sSupportEmail' => $sSupportEmail,
            'sSupportPhone' => $sSupportPhone,
            );
    }
}
