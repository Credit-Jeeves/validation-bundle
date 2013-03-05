<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class CarController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $Report = $cjUser->getReport()->last();
        $nTargetScore   = $cjUser->getLead()->getTargetScore();
        //$nTargetScore = 0;
        return $this->render('ComponentBundle:Car:index.html.twig', array('nTargetScore' => $nTargetScore));
    }
}
