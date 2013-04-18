<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class IncentivesController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function indexAction()
    {
        $cjUser = $this->getUser();
        

//         $cjApplicantReport = $this->getUser()
//         ->getCjApplicant()
//         ->getCjApplicantReportPrequals()
//         ->getLast();
//         $aDirectCheck            = $cjApplicantReport->getApplicantDirectCheck();
//         // Prepare two additional arrays in order not to do many queries
//         $aApplicantNegativeTradelines = cjApplicantTradelinesTable::getInstance()->findBy(
//                 'cj_applicant_id',
//                 $this->getUser()->getCjApplicant()->getId()
//         ); // query number 1
//         $aNegativeCollection = array();
//         foreach ($aApplicantNegativeTradelines as $oItem) {
//             $aNegativeCollection[$oItem->getTradeline()] = $oItem;
//         }
//         $ApplicantIncentives = cjApplicantIncentivesTable::getInstance()->findBy(
//                 'cj_applicant_id',
//                 $this->getUser()->getCjApplicant()->getId()
//         ); // query number 2
//         $aIncentivesCollection = array();
//         foreach ($ApplicantIncentives as $oItem) {
//             $aIncentivesCollection[$oItem->getCjTradelineId()] = $oItem;
//         }
//         // Result arrays for the template
//         $this->aNegativeTradelines   = array();
//         $this->aIncentivesTradelines = array();
//         // Get negative tradelines
//         $allNegativeTradelines = $this->getUser()
//         ->getCjApplicant()
//         ->getCjApplicantReportPrequals()
//         ->getLast()
//         ->getApplicantNegativeTradeLines(false);
//         foreach ($allNegativeTradelines as $aTradeline) {
//             // we'll work only with opened tradelines
//             if ($aTradeline['tr_state'] == 'C' & !in_array($aTradeline['tr_status'], array(93, 97))) {
//                 continue;
//             }
        
//             $aTradeline     = cjApplicantTradelines::prepareTradeline($aTradeline);
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
//         }
//         // Get satisfactory tradelines
//         $allSatisfactoryTradelines = $this->getUser()
//         ->getCjApplicant()
//         ->getCjApplicantReportPrequals()
//         ->getLast()
//         ->getApplicantSatisfactoryTradeLines(false);
//         foreach ($allSatisfactoryTradelines as $aTradeline) {
//             // we'll work only with opened tradelines
//             if ($aTradeline['tr_state'] == 'C') {
//                 continue;
//             }
//             $aTradeline = cjApplicantTradelines::prepareTradeline($aTradeline);
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
        
//         }
//         $this->jsonIncentivesTradelines = json_encode($this->aIncentivesTradelines);
//         $this->jsonNegativeTradelines   = json_encode($this->aNegativeTradelines);        
        
        
        $aIncentivesTradelines = array();
        $aNegativeTradelines = array();
        
        $nTotal = count($aIncentivesTradelines + $aNegativeTradelines) ? true : false;
        
        
        
        $jsonIncentivesTradelines = json_encode($aIncentivesTradelines);
        $jsonNegativeTradelines = json_encode($aNegativeTradelines);
        
        
        
        
        
       // $Report = $cjUser->getReports()->last();
        $name   = '***';///$Report->getRawData();
        return array(
            'nTotal' => $nTotal,
            'jsonIncentivesTradelines' => $jsonIncentivesTradelines,
            'jsonNegativeTradelines' => $jsonNegativeTradelines,
            );
    }
}
