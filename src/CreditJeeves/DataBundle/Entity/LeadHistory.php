<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\LeadHistory as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * LeadHistory
 *
 * @ORM\Table(name="cj_lead_history")
 * @ORM\Entity
 */
class LeadHistory extends Base
{
    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        if ( isset($data['target_score'])) {
            $this->setTargetScore($data['target_score']);
        }
        if ( isset($data['target_name'])) {
            $this->setTargetName($data['target_name']);
        }
        if ( isset($data['target_url'])) {
            $this->setTargetUrl($data['target_url']);
        }
        if ( isset($data['state'])) {
            $this->setState($data['state']);
        }
        if ( isset($data['trade_in'])) {
            $this->setTradeIn($data['trade_in']);
        }
        if ( isset($data['down_payment'])) {
            $this->setDownPayment($data['down_payment']);
        }
        if ( isset($data['fraction'])) {
            $this->setFraction($data['fraction']);
        }
        if ( isset($data['status'])) {
            $this->setStatus($data['status']);
        }
        if ( isset($data['updated_at'])) {
            $this->setUpdatedAt($data['updated_at']);
        }
    }
}
