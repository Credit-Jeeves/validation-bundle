<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class ScoreController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $cjLead = $cjUser->getLeads()->last();
        $nTargetScore = $cjLead->getTargetScore();
        $aScores = $cjUser->getScores();
        
        $chartData = array();
        foreach ($aScores as $score){
            $chartData[] = sprintf("[\"%s\", %d, %d]", $score->getCreatedDate()->format('M d, Y'), $score->getScore(), $nTargetScore);
        }
        $chartData = implode(',', $chartData);
        $nScore = $cjUser->getScores()->last()->getScore();
        $sDate = $cjUser->getScores()->last()->getCreatedDate()->format('M d, Y');
        return $this->render(
            'ComponentBundle:Score:index.html.twig',
            array(
                'chartData' => $chartData,
                'nScore' => $nScore,
                'nTargetScore' => $nTargetScore,
                'sDate' => $sDate,
               )
           );
    }
}
