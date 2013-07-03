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
    public function indexAction($user)
    {
        $aScores = $user->getScores();
        
        $chartData = array();
        foreach ($aScores as $score) {
            $nScore = $score->getScore();
            $nScore = ($nScore > 900) ? 900 : $nScore;
            $chartData[] = sprintf(
                "[\"%s\", %d]",
                $score->getCreatedDate()->format('M d, Y'),
                $nScore
            );
        }
        $chartData = implode(',', $chartData);
        $nScore = $user->getScores()->last()->getScore();
        $nScore = ($nScore > 900) ? 900 : $nScore;
        $nFicoScore = $user->getScores()->last()->getFicoScore();
        $sDate = $user->getScores()->last()->getCreatedDate()->format('M d, Y');
        return array(
            'chartData' => $chartData,
            'nScore' => $nScore,
            'nFicoScore' => $nFicoScore,
            'sDate' => $sDate,
        );
    }
}
