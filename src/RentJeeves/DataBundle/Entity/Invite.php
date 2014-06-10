<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Invite as Base;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Invite
 *
 * @ORM\Table(name="rj_invite")
 * @ORM\Entity
 */
class Invite extends Base
{
    /**
     * @Assert\Callback(
     *     groups={
     *         "invite",
     *     }
     * )
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ((!$this->isSingle && empty($this->unit)) || ($this->isSingle && !empty($this->unit))) {
            $context->addViolationAt('unit', 'invite.error.specify_unit_or_mark_single');
        }
    }

    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }
}
