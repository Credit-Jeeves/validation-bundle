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

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
    }
    
}
