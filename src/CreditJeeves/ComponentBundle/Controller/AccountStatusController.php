<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class AccountStatusController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $Report = $cjUser->getReportsPrequal()->last();
        $name   = '***';//$Report->getRawData();
        return $this->render('ComponentBundle:AccountStatus:index.html.twig', array('name' => $name));
    }
}
