<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Dealer extends User
{
    public function getHoldingName()
    {
        $holding = $this->getHolding();
        return $holding ? $holding->getName(): '';
    }
}
