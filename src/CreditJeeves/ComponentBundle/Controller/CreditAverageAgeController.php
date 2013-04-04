<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CreditAverageAgeController extends Controller
{
    public function indexAction()
    {
        $nOldest = 0;
        $nAge = 0;
        $nTotal = 0;
        $aTradelines = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last()->getTradeLines();
        $oCurrentDate = new \DateTime('now');
        foreach ($aTradelines as $aTradeline) {
            $nTotal++;
            $oOpenedDate = \DateTime::createFromFormat('my', $aTradeline['date_open']);
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
