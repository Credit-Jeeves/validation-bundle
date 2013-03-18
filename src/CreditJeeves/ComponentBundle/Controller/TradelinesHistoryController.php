<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TradelinesHistoryController extends Controller
{
    public function indexAction()
    {
        $Report    = $this->getUser()->getReportsPrequal()->last();
        
        $aNegativeTradelines     = $Report->getApplicantNegativeTradeLines();
        $aSatisfactoryTradelines = $Report->getApplicantSatisfactoryTradeLines();
        $aDirectCheck            = $Report->getApplicantDirectCheck();
        $aMonthes = array();
        for($i = 1; $i < 13; $i++) {
            $aMonthes[] = date('M', mktime(0,0,0, $i));
        }
        $aClosedTradelines = array();
        // Create closed tradelines
        foreach ($aNegativeTradelines as $nKey => $aTradeline) {
            if (isset($aTradeline['tr_subcode']) && isset($aDirectCheck[$aTradeline['tr_subcode']])) {
                $aTradeline['direct_check']['subscriber_phone_number'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_phone_number'];
                $aTradeline['direct_check']['subscriber_address'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_address'];
                $aTradeline['direct_check']['subscriber_city'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_city'];
                $aTradeline['direct_check']['subscriber_state'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_state'];
                $aTradeline['direct_check']['subscriber_zip_code'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_zip_code'];
            }
            if ($aTradeline['tr_state'] == 'C' && !in_array($aTradeline['tr_status'], array(93, 97))) {
                $aClosedTradeLines[] = $aTradeline;
                unset($aNegativeTradeLines[$nKey]);
            } else {
                $aNegativeTradelines[$nKey] = $aTradeline;
            }
        }
        foreach ($aSatisfactoryTradelines as $nKey => $aTradeline) {
            if (isset($aTradeline['tr_subcode']) && isset($aDirectCheck[$aTradeline['tr_subcode']])) {
                $aTradeline['direct_check']['subscriber_phone_number'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_phone_number'];
                $aTradeline['direct_check']['subscriber_address'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_address'];
                $aTradeline['direct_check']['subscriber_city'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_city'];
                $aTradeline['direct_check']['subscriber_state'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_state'];
                $aTradeline['direct_check']['subscriber_zip_code'] = $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_zip_code'];
            }
            if ($aTradeline['tr_state'] == 'C') {
                $aClosedTradelines[] = $aTradeline;
                unset($aSatisfactoryTradelines[$nKey]);
            } else {
                $aSatisfactoryTradelines[$nKey] = $aTradeline;
            }
        }
        return $this->render(
            'ComponentBundle:TradelinesHistory:index.html.twig',
            array(
                'aNegativeTradelines' => $aNegativeTradelines,
                'aSatisfactoryTradelines' => $aSatisfactoryTradelines,
                'aClosedTradelines' => $aClosedTradelines,
                'aMonthes' => $aMonthes,
                )
            );
    }
}
