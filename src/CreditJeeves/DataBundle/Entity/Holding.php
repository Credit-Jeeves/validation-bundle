<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Holding as BaseHolding;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\HoldingRepository")
 * @ORM\Table(name="cj_holding")
 * @ORM\HasLifecycleCallbacks()
 */
class Holding extends BaseHolding
{
    public function __toString()
    {
        return $this->getName() ?: '';
    }
}
