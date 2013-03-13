<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomepageController extends Controller
{
    public function indexAction()
    {
        $sRouteName = $this->get('request')->get('_route');
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $sEmail = $cjUser->getEmail();
        return $this->render('ApplicantBundle:Homepage:index.html.twig', array('sEmail' => $sEmail, 'sRouteName' => $sRouteName));
    }
}
