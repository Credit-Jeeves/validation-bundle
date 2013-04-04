<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MissedPaymentsController extends Controller
{
    public function indexAction()
    {
        $nTotal = 0;
        $nLate = 0;
        
        $aTradelines = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last()->getTradeLines();
        foreach ($aTradelines as $aTradeline) {
            $aTradeline['months_reviewed'] = isset($aTradeline['months_reviewed']) ? $aTradeline['months_reviewed'] : 0;
            $aTradeline['30_day_counter'] = isset($aTradeline['30_day_counter']) ? $aTradeline['30_day_counter'] : 0;
            $aTradeline['60_day_counter'] = isset($aTradeline['60_day_counter']) ? $aTradeline['60_day_counter'] : 0;
            $aTradeline['90_day_counter'] = isset($aTradeline['90_day_counter']) ? $aTradeline['90_day_counter'] : 0;
            $nTotal += intval($aTradeline['months_reviewed']);
            $nLate += (intval($aTradeline['30_day_counter']))
            + (intval($aTradeline['60_day_counter']))
            + (intval($aTradeline['90_day_counter']));
        }
        $nPercent = round($nLate * 100 / $nTotal);
        return $this->render(
            'ComponentBundle:MissedPayments:index.html.twig',
            array(
                'nPercent' => $nPercent,
                'nTotal' => $nTotal,
                'nLate' => $nLate,
            )
        );
    }
}
