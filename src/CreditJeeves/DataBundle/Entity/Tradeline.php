<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Tradeline as BaseTradeline;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\TradelineRepository")
 * @ORM\Table(name="cj_applicant_tradelines")
 * @ORM\HasLifecycleCallbacks()
 */
class Tradeline extends BaseTradeline
{
    /**
     *
     * @param array $aTradeline
     */
    public static function prepareTradeline($aTradeline)
    {
        // Calculate additional items
        $aTradeline['usage'] = 0;
        $aTradeline['limit'] = 0;
        $nLimit = isset($aTradeline['credit_amounts']['credit_limit'])
            ? intval($aTradeline['credit_amounts']['credit_limit']) : 0;
        if ($nLimit > 0) {
            $aTradeline['usage'] = intval($aTradeline['tr_balance']) / $nLimit;
            $aTradeline['limit'] = $nLimit;
        }
        $aTradeline['tr_acctnum'] = isset($aTradeline['tr_acctnum']) ? $aTradeline['tr_acctnum'] : 'XXXX';
        $aTradeline['account']    = isset($aTradeline['account']) ? $aTradeline['account'] : 'XXXX';
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

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
    }

    public static function formatTradelineForIncentive($aTradeline, $aNegative, $aDirectCheck)
  {
    $aStatusLate30Days  = array('71', '72', '73', '74', '75', '76', '77');
    $aStatusLate60Days  = array('78', '79');
    $aStatusLate90Days  = array('80');
    $aStatusLate120Days = array('81', '82');
    $aStatusLate150Days = array('83');
    $aStatusLate180Days = array('84');
    $aStatusCollection  = array('93');
    $aStatusChargeOff   = array('97');
    // Default
    $aTradeline['title']        = 'Late Payment';
    $aTradeline['days_late']    = 30;
    $aTradeline['sub_title']    = 'Days Late';
    $aTradeline['points']       = '30-60';
    $aTradeline['display']      = 'late';
    $aTradeline['action']       = 'Pay';
    $aTradeline['diff']         = 0;
    $aTradeline['is_fixed']     = $aNegative->getIsFixed();
    $aTradeline['is_disputed']  = $aNegative->getIsDisputed();
    $aTradeline['is_completed'] = $aNegative->getIsCompleted();
    $aTradeline['id']           = $aNegative->getId();
    $aTradeline['phone']        = isset($aDirectCheck[$aTradeline['tr_subcode']]['subscriber_phone_number']) ? $aDirectCheck[$aTradeline['tr_subcode']]['subscriber_phone_number'] : false;
    // Charge Off
    if (in_array($aTradeline['tr_status'], $aStatusChargeOff)) {
      $aTradeline['sub_title']  = '';
      $aTradeline['title']      = 'Charge Off';
      $aTradeline['points']     = '40-60';
      $aTradeline['display']    = 'chargeoff';
      $aTradeline['action']     = 'Settle';
      return $aTradeline;
    }
    // Collection
    if (in_array($aTradeline['tr_status'], $aStatusCollection)) {
      $aTradeline['sub_title']  = '';
      $aTradeline['title']      = 'Collection';
      $aTradeline['points']     = '40-60';
      $aTradeline['display']    = 'collection';
      $aTradeline['action']     = 'Settle';
      return $aTradeline;
    }
    // 180 Days late
    if (in_array($aTradeline['tr_status'], $aStatusLate180Days)) {
      $aTradeline['days_late']    = 180;
      return $aTradeline;
    }
    // 150 Days late
    if (in_array($aTradeline['tr_status'], $aStatusLate150Days)) {
      $aTradeline['days_late']    = 150;
      return $aTradeline;
    }
    // 120 Days late
    if (in_array($aTradeline['tr_status'], $aStatusLate120Days)) {
      $aTradeline['days_late']    = 120;
      return $aTradeline;
    }
    // 90 Days late
    if (in_array($aTradeline['tr_status'], $aStatusLate90Days)) {
      $aTradeline['days_late']    = 90;
      return $aTradeline;
    }
    // 60 Days late
    if (in_array($aTradeline['tr_status'], $aStatusLate60Days)) {
      $aTradeline['days_late']    = 60;
      return $aTradeline;
    }
    // 30 Days late
    if (in_array($aTradeline['tr_status'], $aStatusLate30Days)) {
      return $aTradeline;
    }
    // This is quick fix more changes in the cjApplicant tradeline class lines55-72
    $aTradeline['title']        = '';
    $aTradeline['days_late']    = 0;
    $aTradeline['sub_title']    = '';
    $aTradeline['points']       = '10-25';
    $aTradeline['display']      = '';
    $aTradeline['action']       = 'Pay';
    $aTradeline['diff']         = 0;
    
    if ($aTradeline['usage'] >= 0.3) {
      $aTradeline['sub_title'] = '';
      $aTradeline['diff']      = $aTradeline['tr_balance'] - .3 * $aTradeline['limit'];
      $aTradeline['title']     = 'High Balance';
      $aTradeline['points']    = '10-25';
      $aTradeline['display']   = 'high';
    }
    return $aTradeline;
  }
}
