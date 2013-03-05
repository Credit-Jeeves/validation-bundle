<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class ScoreController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $aScores = $cjUser->getScore();
        $nScore = '0';//$cjUser->getReport()->last()->getRawData();
        return $this->render('ComponentBundle:Score:index.html.twig', array('nScore' => $nScore));
    }
}
