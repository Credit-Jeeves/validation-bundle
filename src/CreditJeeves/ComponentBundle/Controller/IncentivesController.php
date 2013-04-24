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
    public function indexAction(\CreditJeeves\DataBundle\Entity\Lead $Lead)
    {
        // Default data
        $aNegativeCollection = array();
        $aIncentivesCollection = array();
        $aNegativeTradelines = array();
        $aIncentivesTradelines = array();
        
        // Get User's Report
        $cjUser = $this->get('core.session.applicant')->getUser();
        $Report = $cjUser->getReportsPrequal()->last();
        $sDate = $Report->getCreatedAt()->format('M j, Y');
        $ArfReport = $Report->getArfReport();
        // Get direct check
        $aDirectCheck = $Report->getApplicantDirectCheck();
        // Negative and satisfactory tradelines
        $aReportNegativeTradelines     = $Report->getApplicantNegativeTradeLines();
        $aReportSatisfactoryTradelines = $Report->getApplicantSatisfactoryTradeLines();
        // Negative collection
        $aApplicantNegativeTradelines = $this->
            getDoctrine()->
            getRepository('DataBundle:Tradeline')->
            findBy(
                array(
                    'cj_applicant_id' => $cjUser->getId(), 
                    'cj_group_id' => $Lead->getCjGroupId(),
                    )
            );
        foreach ($aApplicantNegativeTradelines as $oItem) {
            $aNegativeCollection[$oItem->getTradeline()] = $oItem;
        }
        // Incentives collection
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
        // Start to create data for the page
        // Negative tradelines
        foreach ($aReportNegativeTradelines as $aTradeline) {
            if ($aTradeline['tr_state'] == 'C' & !in_array($aTradeline['tr_status'], array(93, 97))) {
              continue;
            }
            $aTradeline     = Tradeline::prepareTradeline($aTradeline);
            $sTradelineHash = md5($aTradeline['tr_subcode'].$aTradeline['account']);
            if (!isset($aNegativeCollection[$sTradelineHash])) {
              // Here would be add this tradeline
              // Why we do this? Applicant could add one more lead after report;
              $aNegativeCollection[$sTradelineHash] = $this->addTradeline($aTradeline, $Lead);
            }
            $aTradeline = Tradeline::formatTradelineForIncentive($aTradeline, $aNegativeCollection[$sTradelineHash], $aDirectCheck);
            $isCompleted    = $aNegativeCollection[$sTradelineHash]->getIsCompleted();
            if (!$isCompleted) {
                $aNegativeTradelines[] = $aTradeline;
            } else {
                $aTradeline['incentive'] = isset($aIncentivesCollection[$aTradeline['id']])
                ? $aIncentivesCollection[$aTradeline['id']]->getCjGroupIncentives()->getText() : '';
                $aIncentivesTradelines[] = $aTradeline;
            }
            
        }
        // Get satisfactory tradelines
         foreach ($aReportSatisfactoryTradelines as $aTradeline) {
             // we'll work only with opened tradelines
            if ($aTradeline['tr_state'] == 'C') {
                continue;
            }
             $aTradeline = Tradeline::prepareTradeline($aTradeline);
            $sTradelineHash = md5($aTradeline['tr_subcode'].$aTradeline['account']);
            $nTradelineId = isset($aNegativeCollection[$sTradelineHash]) ? $aNegativeCollection[$sTradelineHash]->getId() : 0;
            if (!empty($nTradelineId)) {
                $aTradeline = $this->formatTradeline($aTradeline, $aNegativeCollection[$sTradelineHash], $aDirectCheck);
                $aTradeline['incentive'] = isset($aIncentivesCollection[$aTradeline['id']])
                ? $aIncentivesCollection[$aTradeline['id']]->getCjGroupIncentives()->getText() : '';
                if ($aTradeline['is_completed']) {
                    $aIncentivesTradelines[] = $aTradeline;
                } else {
                    $aNegativeTradelines[] = $aTradeline;
                }
            }
         }
        $nTotal = count($aIncentivesTradelines + $aNegativeTradelines) ? true : false;
        $jsonIncentivesTradelines = json_encode($aIncentivesTradelines);
        $jsonNegativeTradelines = json_encode($aNegativeTradelines);
        return array(
            'nTotal' => $nTotal,
            'jsonIncentivesTradelines' => $jsonIncentivesTradelines,
            'jsonNegativeTradelines' => $jsonNegativeTradelines,
            );
    }

    private function addTradeline($aTradeline, $Lead)
    {
        $em = $this->getDoctrine()->getManager();
        $tradeline = new Tradeline();
        $tradeline->setCjGroupId($Lead->getCjGroupId());
        $tradeline->setUser($Lead->getUser());
        $tradeline->setTradeline(md5($aTradeline['tr_subcode'].$aTradeline['account']));
        $tradeline->setStatus($aTradeline['tr_status']);
        $em->persist($tradeline);
        $em->flush();
        return $tradeline;
    }

    
    /**
     *
     * @param array $aTradeline
     */
    public static function prepareTradeline($aTradeline)
    {
        // Calculate additional items
        $aTradeline['usage'] = 0;
        $aTradeline['limit'] = 0;
        $nLimit = isset($aTradeline['credit_amounts']['credit_limit']) ? intval($aTradeline['credit_amounts']['credit_limit']) : 0;
        if ($nLimit > 0) {
            $aTradeline['usage'] = intval($aTradeline['tr_balance']) / $nLimit;
            $aTradeline['limit'] = $nLimit;
        }
        $aTradeline['tr_acctnum'] = isset($aTradeline['tr_acctnum']) ? $aTradeline['tr_acctnum'] : 'XXXX'; // need to display on the page
        $aTradeline['account']    = isset($aTradeline['account']) ? $aTradeline['account'] : 'XXXX'; // need for the hash
        // unset unnecessary items
        unset($aTradeline['payment_history']);
        unset($aTradeline['credit_amounts']);
        unset($aTradeline['30_day_counter']);
        unset($aTradeline['60_day_counter']);
        unset($aTradeline['90_day_counter']);
        unset($aTradeline['derog_counter']);
        unset($aTradeline['ecoa']);
        unset($aTradeline['kob']);
        unset($aTradeline['tr_amount1']);
        unset($aTradeline['tr_amount1_qual']);
        unset($aTradeline['tr_amount2']);
        unset($aTradeline['tr_amount2_qual']);
        unset($aTradeline['special_comment_code']);
        return $aTradeline;
    }
    
}
