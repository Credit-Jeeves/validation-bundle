<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class DidYouKnowController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $Report = $cjUser->getReport()->last();
        $name   = $Report->getRawData();
        return $this->render('ComponentBundle:DidYouKnow:index.html.twig', array('name' => $name));
    }
}
