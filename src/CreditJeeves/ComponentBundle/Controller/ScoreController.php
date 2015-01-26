<?php
namespace CreditJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Lead;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\DataBundle\Entity\Report;

class ScoreController extends Controller
{
    /**
     * @Template()
     *
     * @param Tenant $user
     *
     * @return array
     */
    public function indexAction($user, Report $Report)
    {
        $scores = $user->getScores();

        $chartData = array();
        foreach ($scores as $score) {
            $scoreDatum = $score->getScore();
            $scoreDatum = ($scoreDatum > 900) ? 900 : $scoreDatum;
            $chartData[] = sprintf(
                "[\"%s\", %d]",
                $score->getCreatedDate()->format('M d, Y'),
                $scoreDatum
            );
        }
        $chartData = implode(',', $chartData);
        $score = $user->getScores()->last()->getScore();
        $score = ($score > 900) ? 900 : $score;
        $date = $user->getScores()->last()->getCreatedDate()->format('M d, Y');

        return array(
            'creditTrackEnabled' => $user->getSettings()->isCreditTrack(),
            'chartData' => $chartData,
            'score' => $score,
            'date' => $date,
            'bureau' => $Report->getBureauName()
        );
    }
}
