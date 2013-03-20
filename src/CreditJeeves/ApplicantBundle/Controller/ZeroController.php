<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * This page would be usefull for development
 * @author Alex
 * @Route("/")
 */
class ZeroController extends Controller
{
    /**
     * @Route("/zero", name="applicant_zero_page")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $sEmail = $this->getUser()->getEmail();
        return array(
             'sEmail' => $sEmail
            );
    }
}
