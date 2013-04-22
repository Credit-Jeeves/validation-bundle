<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Tradeline;


class IncentivesController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function indexAction()
    {
        $aNegativeCollection = array();
        $aNegativeTradelines = array();
        $aIncentivesCollection = array();
        $aIncentivesTradelines = array();
        
        $cjUser = $this->get('core.session.applicant')->getUser();
        $Report = $cjUser->getReportsPrequal()->last();
        $sDate = $Report->getCreatedAt()->format('M j, Y');
        $ArfReport = $Report->getArfReport();        
        $aDirectCheck = $Report->getApplicantDirectCheck();
        
        $aNegativeTradelines = $Report->getApplicantNegativeTradeLines();
        $aSatisfactoryTradelines = $Report->getApplicantSatisfactoryTradeLines();
        $aApplicantNegativeTradelines = $this->
            getDoctrine()->
            getRepository('DataBundle:Tradeline')->
            findBy(
                array(
                    'cj_applicant_id' => $cjUser->getId()
                    )
                );
         foreach ($aApplicantNegativeTradelines as $oItem) {
             $aNegativeCollection[$oItem->getTradeline()] = $oItem;
         }
         $aApplicantIncentives = $this->
             getDoctrine()->
             getRepository('DataBundle:ApplicantIncentive')->
             findBy(
                 array(
                     'cj_applicant_id' => $cjUser->getId()
                     )
                 );
         foreach ($aApplicantIncentives as $oItem) {
             $aIncentivesCollection[$oItem->getCjTradelineId()] = $oItem;
         }
         // Result arrays for the template
         
//         // Get negative tradelines
//         $allNegativeTradelines = $this->getUser()
//         ->getCjApplicant()
//         ->getCjApplicantReportPrequals()
//         ->getLast()
//         ->getApplicantNegativeTradeLines(false);
         foreach ($aNegativeTradelines as $aTradeline) {
//             // we'll work only with opened tradelines
             if ($aTradeline['tr_state'] == 'C' & !in_array($aTradeline['tr_status'], array(93, 97))) {
                 continue;
             }
        
             $aTradeline     = Tradeline::prepareTradeline($aTradeline);
             $sTradelineHash = md5($aTradeline['tr_subcode'].$aTradeline['account']);
             //echo $sTradelineHash;
             if (!isset($aNegativeCollection[$sTradelineHash])) {
                 continue;
             }
//              echo '<pre>';
//              print_r($aTradeline);
//              echo '</pre>';
             $aTradeline = Tradeline::formatTradelineForIncentive($aTradeline, $aNegativeCollection[$sTradelineHash], $aDirectCheck);
             $aIncentivesTradelines[] = $aTradeline;
             //             $isCompleted    = $aNegativeCollection[$sTradelineHash]->getIsCompleted();
//             $aTradeline = $this->formatTradeline($aTradeline, $aNegativeCollection[$sTradelineHash], $aDirectCheck);
//             if (empty($aTradeline['display'])) {
//                 continue;
//             }
//             if (!$isCompleted) {
//                 $this->aNegativeTradelines[] = $aTradeline;
//             } else {
//                 $aTradeline['incentive'] = isset($aIncentivesCollection[$aTradeline['id']])
//                 ? $aIncentivesCollection[$aTradeline['id']]->getCjGroupIncentives()->getText() : '';
//                 $this->aIncentivesTradelines[] = $aTradeline;
//             }
         }
//         // Get satisfactory tradelines
//         $allSatisfactoryTradelines = $this->getUser()
//         ->getCjApplicant()
//         ->getCjApplicantReportPrequals()
//         ->getLast()
//         ->getApplicantSatisfactoryTradeLines(false);
         foreach ($aSatisfactoryTradelines as $aTradeline) {
             // we'll work only with opened tradelines
            if ($aTradeline['tr_state'] == 'C') {
                //continue;
            }
             $aTradeline = Tradeline::prepareTradeline($aTradeline);
//              echo '<pre>';
//              print_r($aTradeline);
//              echo '</pre>';
              
//             $sTradelineHash = md5($aTradeline['tr_subcode'].$aTradeline['account']);
//             $nTradelineId = isset($aNegativeCollection[$sTradelineHash]) ? $aNegativeCollection[$sTradelineHash]->getId() : 0;
//             if (!empty($nTradelineId)) {
//                 $aTradeline = $this->formatTradeline($aTradeline, $aNegativeCollection[$sTradelineHash], $aDirectCheck);
//                 $aTradeline['incentive']    = isset($aIncentivesCollection[$aTradeline['id']])
//                 ? $aIncentivesCollection[$aTradeline['id']]->getCjGroupIncentives()->getText() : '';
//                 if ($aTradeline['is_completed']) {
//                     $this->aIncentivesTradelines[] = $aTradeline;
//                 } else {
//                     $this->aNegativeTradelines[] = $aTradeline;
//                 }
//             }
        
         }
//         $this->jsonIncentivesTradelines = json_encode($this->aIncentivesTradelines);
//         $this->jsonNegativeTradelines   = json_encode($this->aNegativeTradelines);        
        
        
        $nTotal = count($aIncentivesTradelines + $aNegativeTradelines) ? true : false;
        $jsonIncentivesTradelines = json_encode($aIncentivesTradelines);
        $jsonNegativeTradelines = json_encode($aNegativeTradelines);
        return array(
            'nTotal' => $nTotal,
            'jsonIncentivesTradelines' => $jsonIncentivesTradelines,
            'jsonNegativeTradelines' => $jsonNegativeTradelines,
            );
    }

//     /**
//      *
//      * @param array $aTradeline
//      */
//     public static function prepareTradeline($aTradeline)
//     {
//         // Calculate additional items
//         $aTradeline['usage'] = 0;
//         $aTradeline['limit'] = 0;
//         $nLimit = isset($aTradeline['credit_amounts']['credit_limit']) ? intval($aTradeline['credit_amounts']['credit_limit']) : 0;
//         if ($nLimit > 0) {
//             $aTradeline['usage'] = intval($aTradeline['tr_balance']) / $nLimit;
//             $aTradeline['limit'] = $nLimit;
//         }
//         $aTradeline['tr_acctnum'] = isset($aTradeline['tr_acctnum']) ? $aTradeline['tr_acctnum'] : 'XXXX'; // need to display on the page
//         $aTradeline['account']    = isset($aTradeline['account']) ? $aTradeline['account'] : 'XXXX'; // need for the hash
//         // unset unnecessary items
//         unset($aTradeline['payment_history']);
//         unset($aTradeline['credit_amounts']);
//         unset($aTradeline['30_day_counter']);
//         unset($aTradeline['60_day_counter']);
//         unset($aTradeline['90_day_counter']);
//         unset($aTradeline['derog_counter']);
//         unset($aTradeline['ecoa']);
//         unset($aTradeline['kob']);
//         unset($aTradeline['tr_amount1']);
//         unset($aTradeline['tr_amount1_qual']);
//         unset($aTradeline['tr_amount2']);
//         unset($aTradeline['tr_amount2_qual']);
//         unset($aTradeline['special_comment_code']);
//         return $aTradeline;
//     }
    
}
