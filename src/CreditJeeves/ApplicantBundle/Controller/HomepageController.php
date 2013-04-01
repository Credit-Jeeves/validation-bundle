<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\DataBundle\Entity\Lead;

/**
 * @author Alex
 * @Route("/")
 */
class HomepageController extends Controller
{
    /**
     * @Route("/", name="applicant_homepage")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $nLeadId = $this->get('core.session.applicant')->getLeadId();
        $Lead    = $this->getDoctrine()->getRepository('DataBundle:Lead')->find($nLeadId);
        $Report  = $this->getUser()->getReportsPrequal()->last();
        $sEmail  = $this->getUser()->getEmail();
        return array(
            'Report' => $Report,
            'Lead' => $Lead,
            'sEmail' => $sEmail
            );
    }
}
