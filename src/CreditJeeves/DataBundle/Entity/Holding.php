<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Model\Holding as BaseHolding;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\AccountingSystem;

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

    /**
     * @return array
     */
    public function getHoldingAdmin()
    {
        $usersAdmin = [];

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
    public function isAllowedToSendRealTimePayments()
    {
        if (!$this->getExternalSettings()) {
            return false;
        }

        return $this->getExternalSettings()->isAllowedToSendRealTimePayments();
    }

    /**
     * @return boolean
     */
    public function isApiIntegrated()
    {
        return array_key_exists($this->getAccountingSystem(), AccountingSystem::$integratedWithApi);
    }
}
