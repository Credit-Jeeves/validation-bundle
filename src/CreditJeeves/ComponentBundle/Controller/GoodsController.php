<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class GoodsController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $Report = $cjUser->getReportsPrequal()->last();
        $nTargetScore   = $cjUser->getLeads()->last()->getTargetScore();
        //$nTargetScore = $Report->getRawData();
        return $this->render('ComponentBundle:Goods:index.html.twig', array('nTargetScore' => $nTargetScore));
    }
}
