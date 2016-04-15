<?php
namespace CreditJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LeadController extends Controller
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
        if (empty($chartData)) {
            $chartData = array('', '', '');
        }
        $chartData = implode(',', $chartData);
        $nScore = $cjUser->getLastScore();
        $nFicoScore = $this->getLastFicoScore($cjUser);
        $sDate = $cjUser->getScores()->last()->getCreatedDate()->format('M d, Y');
        $nTop = intval((850 - $nTargetScore) * 171 / 600);

        return array(
            'chartData' => $chartData,
            'nScore' => $nScore ? $nScore : 'N/A',
            'nFicoScore' => $nFicoScore ? $nFicoScore : 'N/A',
            'nTargetScore' => $nTargetScore,
            'nTop' => $nTop,
            'sDate' => $sDate,
        );
    }

    /**
     * @param User $user
     *
     * @return float|int
     */
    private function getLastFicoScore(User $user)
    {
        $score = $user->getLastScore();
        $nFicoScore = round(10 * (($score - 483.06) / 11.079) + 490);
        $nFicoScore = $nFicoScore > 850 ? 850 : $nFicoScore;

        return $score ? $nFicoScore : 0;
    }
}
