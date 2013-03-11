<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TabsController extends Controller
{
    public function indexAction($sRouteName = 'applicant_password')
    {
        return $this->render('ApplicantBundle:Tabs:index.html.twig', array('sRouteName' => $sRouteName));
    }
}
