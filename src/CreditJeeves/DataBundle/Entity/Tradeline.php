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
     * @ORM\PrePersist
     */
    public function prePersist()
    {
    }
}
