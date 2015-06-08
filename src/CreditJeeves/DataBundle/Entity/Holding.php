<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Model\Holding as BaseHolding;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function getHoldingAdmin()
    {
        $usersAdmin = array();

        if (empty($this->users)) {
            return $usersAdmin;
        }

        foreach ($this->users as $user) {
            if (!$user->getIsSuperAdmin()) {
                continue;
            }

            $usersAdmin[] = $user;
        }

        return $usersAdmin;
    }

    public function getLandlords()
    {
        $landlords = new ArrayCollection();
        foreach ($this->users as $user) {
            if ($user->getType() === UserType::LANDLORD) {
                $landlords->add($user);
            }
        }

        return $landlords;
    }

    /**
     * @return bool
     */
    public function isSettingsAllowToSendRealTime()
    {
        if (!$this->getExternalSettings()) {
            return false;
        }

        return $this->getExternalSettings()->isAllowToSendRealTime();
    }

    public function setAllowToSendRealTime()
    {
        if ($this->getExternalSettings()) {
            $this->getExternalSettings()->setAllowToSendRealTime();
        }
    }
}
