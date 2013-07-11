<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @ORM\Entity
 */
class Dealer extends User
{
    /**
     * @var string
     */
    protected $type = UserType::DEALER;

    public function getHoldingName()
    {
        $holding = $this->getHolding();
        return $holding ? $holding->getName(): '';
    }
}
