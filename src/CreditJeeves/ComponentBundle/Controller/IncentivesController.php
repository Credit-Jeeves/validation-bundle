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
        $cjUser = $this->get('core.session.applicant')->getUser();
        $Report = $cjUser->getReportsPrequal()->last();
        $sDate = $Report->getCreatedAt()->format('M j, Y');
        $ArfReport = $Report->getArfReport();        
        $aDirectCheck = $Report->getApplicantDirectCheck();
        $aNegativeTradelines = $Report->getApplicantNegativeTradeLines();
//         echo '<pre>';
//         print_r($aNegativeTradelines);
//         echo '</pre>';
        
        $aSatisfactoryTradelines = $Report->getApplicantSatisfactoryTradeLines();
        $aNegativeCollection = array();
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
         $aIncentivesCollection = array();
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
         $aNegativeTradelines   = array();
         $aIncentivesTradelines = array();
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
             echo '<pre>';
             print_r($aTradeline);
             echo '</pre>';
//             $sTradelineHash = md5($aTradeline['tr_subcode'].$aTradeline['account']);
//             if (!isset($aNegativeCollection[$sTradelineHash])) {
//                 continue;
//             }
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
                continue;
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
}
