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

    public function getUsers()
    {
        return $this->users;
    }
}
