<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\TrustedLandlord as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_trusted_landlord")
 */
class TrustedLandlord extends Base
{
    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }
}
