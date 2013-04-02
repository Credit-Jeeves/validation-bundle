<?php
namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/")
 */
class HomepageController extends Controller
{
    /**
     * @Route("/", name="admin_homepage")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $Applicant = $this->getDoctrine()->getRepository('DataBundle:User')->find(17);
        $this->get('core.session.applicant')->setUser($Applicant);
        return array();
    }
}
