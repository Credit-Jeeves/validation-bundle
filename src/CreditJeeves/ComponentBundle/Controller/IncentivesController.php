<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Tradeline;
use CreditJeeves\CoreBundle\Arf\ArfTradelines;

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
        $aReportTradelines = $this->sortTradelines($Report, $Lead);
        $aReportNegativeTradelines     = $aReportTradelines['negative'];
        $aReportSatisfactoryTradelines = $aReportTradelines['satisfactory'];
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
            $sTradelineHash = md5($aTradeline['tr_subcode'].$aTradeline['account']);
            if (!isset($aNegativeCollection[$sTradelineHash])) {
                // Here would be add this tradeline
                // Why we do this? Applicant could add one more lead after report;
                $aNegativeCollection[$sTradelineHash] = $this->addTradeline($aTradeline, $Lead);
            }
            $aTradeline = Tradeline::formatTradelineForIncentive(
                $aTradeline,
                $aNegativeCollection[$sTradelineHash],
                $aDirectCheck
            );
            $isCompleted    = $aNegativeCollection[$sTradelineHash]->getIsCompleted();
            if (!$isCompleted) {
                $aNegativeTradelines[] = $aTradeline;
            } else {
                $aTradeline['incentive'] = isset($aIncentivesCollection[$aTradeline['id']])
                ? $aIncentivesCollection[$aTradeline['id']]->getCjGroupIncentive()->getText() : '';
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
            $nTradelineId = isset($aNegativeCollection[$sTradelineHash])
            ? $aNegativeCollection[$sTradelineHash]->getId() : 0;
            if (!empty($nTradelineId)) {
                $aTradeline = Tradeline::formatTradelineForIncentive(
                    $aTradeline,
                    $aNegativeCollection[$sTradelineHash],
                    $aDirectCheck
                );
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
            'sUrl' => $this->generateUrl('insentives_ajax'),
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

    private function sortTradelines($Report, $Lead)
    {
        $aResult = array('negative' => array(), 'satisfactory' => array());
        $aTradelines = $Report->getApplicantTradeLines();
        $tradelineEntity = new ArfTradelines();
        $aNegativeCodes = $tradelineEntity->getNegativeCodes();

        foreach ($aTradelines as $aTradeline) {
            $aTradeline = Tradeline::prepareTradeline($aTradeline);
            $isNegative = false;
            if (in_array($aTradeline['tr_status'], $aNegativeCodes)) {
                $isNegative = true;
            }
            if ($aTradeline['usage'] >= 0.3) {
                $isNegative = true;
            }
            if ($isNegative) {
                $aResult['negative'][] = $aTradeline;
            } else {
                $aResult['satisfactory'][] = $aTradeline;
            }
        }
        return $aResult;
    }
}
