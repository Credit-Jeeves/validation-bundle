<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class CreditAverageAgeController extends Controller
{
    public function indexAction(Report $Report)
    {
        $nOldest = 0;
        $nAge = 0;
        $nTotal = 0;
        $aTradelines = $Report->getTradeLines();
        $oCurrentDate = new \DateTime('now');
        foreach ($aTradelines as $aTradeline) {
            $nTotal++;
            $oOpenedDate = \DateTime::createFromFormat('my', $aTradeline['date_open']);
            if (empty($oOpenedDate)) {
                continue;
            }
            $interval = $oOpenedDate->diff($oCurrentDate);
            $nMonthes = $interval->format('%y') * 12 + $interval->format('%m');
            $nAge += $nMonthes;
            if ($nMonthes > $nOldest) {
                $nOldest = $nMonthes;
            }
        }
        if ($nTotal > 0) {
            $nAge = floor($nAge / ($nTotal * 12));
            $nOldest = floor($nOldest / 12);
        }

        return $this->render(
            'ComponentBundle:CreditAverageAge:index.html.twig',
            array(
                'nOldest' => $nOldest,
                'nAge' => $nAge,
            )
        );
    }
}
