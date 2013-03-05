<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SummaryController extends Controller
{
    /**
     * @Route("/summary", name="_summary")
     * @Template()
     */
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $sEmail = $cjUser->getEmail();
        return $this->render('ApplicantBundle:Summary:index.html.twig', array('sEmail' => $sEmail));
    }
}
