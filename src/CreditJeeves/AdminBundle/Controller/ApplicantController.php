<?php
namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * 
 * @Route("/")
 *
 */
class ApplicantController extends Controller
{
    /**
     * @Route("/cj/applicant/{id}/observe", name="admin_applicant_observe")
     */
    public function observeAction($id = null)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->find($id);
        $this->get('core.session.applicant')->setUser($user);
        $url = $this->get('router')->generate('applicant_homepage');
        return new RedirectResponse($url);
    }

    /**
     * @Route("/cj/applicant/{id}/report", name="admin_applicant_report")
     */
    public function reportAction($id = null)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->find($id);
        return array();
    }
}
