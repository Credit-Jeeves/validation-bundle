<?php
namespace CreditJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Lead;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ScoreController extends Controller
{
    /**
     * @Template()
     * @param \CreditJeeves\DataBundle\Entity\Lead $Lead
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Lead $Lead)
    {
        $nTargetScore = $Lead->getTargetScore() ? $Lead->getTargetScore() : 0;
        $cjUser = $this->get('core.session.applicant')->getUser();
        $aScores = $cjUser->getScores();
        
        $chartData = array();
        foreach ($aScores as $score) {
            $chartData[] = sprintf(
                "[\"%s\", %d, %d]",
                $score->getCreatedDate()->format('M d, Y'),
                $score->getScore(),
                $nTargetScore
            );
        }
        $chartData = implode(',', $chartData);
        $nScore = $cjUser->getScores()->last()->getScore();
        $nFicoScore = $cjUser->getScores()->last()->getFicoScore();
        $sDate = $cjUser->getScores()->last()->getCreatedDate()->format('M d, Y');
        $nTop = intval((850 - $nTargetScore) * 171 / 600);
        return array(
            'chartData' => $chartData,
            'nScore' => $nScore,
            'nFicoScore' => $nFicoScore,
            'nTargetScore' => $nTargetScore,
            'nTop' => $nTop,
            'sDate' => $sDate,
        );
    }
}
