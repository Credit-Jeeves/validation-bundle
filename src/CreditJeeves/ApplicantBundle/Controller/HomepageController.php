<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $sRouteName = $this->get('request')->get('_route');
        $sEmail = $this->getUser()->getEmail();
        return array('sEmail' => $sEmail);
    }
}
