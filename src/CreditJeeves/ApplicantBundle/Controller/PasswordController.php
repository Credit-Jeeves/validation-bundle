<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Alex
 * @Route("/")
 */
class PasswordController extends Controller
{
    /**
     * @Route("/summary", name="applicant_password")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $sEmail = $cjUser->getEmail();
        return array('sEmail' => $sEmail);
    }
}
