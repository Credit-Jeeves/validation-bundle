<?php
namespace CreditJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Lead;
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
        $nFicoScore = $cjUser->getLastFicoScore();
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
}
