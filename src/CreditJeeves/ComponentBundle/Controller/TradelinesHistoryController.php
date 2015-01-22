<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TradelinesHistoryController extends Controller
{
    /**
     *
     * This component currently only supports Experian prequal reports
     *
     * @Template()
     * @param \Report $Report
     */
    public function indexAction(Report $Report)
    {
        $aNegativeTradelines = $Report->getApplicantNegativeTradeLines();
        $aSatisfactoryTradelines = $Report->getApplicantSatisfactoryTradeLines();
        $aIndefiniteTradelines = $Report->getApplicantIndefiniteTradelines();
        $aDirectCheck = $Report->getApplicantDirectCheck();
        $aMonthes = array();
        for ($i = 1; $i < 13; $i++) {
            $aMonthes[] = date('M', mktime(0, 0, 0, $i));
        }

        $directCheckKeys = array(
            'subscriber_phone_number',
            'subscriber_address',
            'subscriber_city',
            'subscriber_state',
            'subscriber_zip_code',
        );

        $aClosedTradelines = array();
        // Create closed tradelines
        foreach ($aNegativeTradelines as $nKey => $aTradeline) {
            if (isset($aTradeline['tr_subcode']) && isset($aDirectCheck[$aTradeline['tr_subcode']])) {
                foreach ($directCheckKeys as $key) {
                    $aTradeline['direct_check'][$key] = $aDirectCheck[$aTradeline['tr_subcode']][$key];
                }
            }
            if ($aTradeline['tr_state'] == 'C' && !in_array($aTradeline['tr_status'], array(93, 97))) {
                $aClosedTradeLines[] = $aTradeline;
                unset($aNegativeTradelines[$nKey]);
            } else {
                $aNegativeTradelines[$nKey] = $aTradeline;
            }
        }
        foreach ($aSatisfactoryTradelines as $nKey => $aTradeline) {
            if (isset($aTradeline['tr_subcode']) && isset($aDirectCheck[$aTradeline['tr_subcode']])) {
                foreach ($directCheckKeys as $key) {
                    $aTradeline['direct_check'][$key] = $aDirectCheck[$aTradeline['tr_subcode']][$key];
                }
            }
            if ($aTradeline['tr_state'] == 'C') {
                $aClosedTradelines[] = $aTradeline;
                unset($aSatisfactoryTradelines[$nKey]);
            } else {
                $aSatisfactoryTradelines[$nKey] = $aTradeline;
            }
        }

        return array(
            'aNegativeTradelines' => $aNegativeTradelines,
            'aSatisfactoryTradelines' => $aSatisfactoryTradelines,
            'aIndefiniteTradelines' => $aIndefiniteTradelines,
            'aClosedTradelines' => $aClosedTradelines,
            'aMonthes' => $aMonthes,
        );
    }
}
