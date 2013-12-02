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

    public function setDealerGroups($dealerGroups)
    {
        if (!is_array($dealerGroups)) {
            $this->addDealerGroup($dealerGroups);
            return;
        }

        foreach ($dealerGroups as $dealerGroup) {
            $this->addDealerGroup($dealerGroup);
        }

    }

    public function canRemove()
    {
        if ($this->getDealerLeads()->count() > 0) {
            return false;
        }

        return true;
    }
}
