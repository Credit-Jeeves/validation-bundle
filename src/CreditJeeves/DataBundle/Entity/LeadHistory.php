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
        $this->setTargetScore($data['target_score']);
        $this->setTargetName($data['target_name']);
        $this->setTargetUrl($data['target_url']);
        $this->setState($data['state']);
        $this->setTradeIn($data['trade_in']);
        $this->setDownPayment($data['down_payment']);
        $this->setFraction($data['fraction']);
        $this->setStatus($data['status']);
        $this->setUpdatedAt($data['updated_at']);
    }
}
