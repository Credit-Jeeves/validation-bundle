<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\CheckMailingAddress as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_check_mailing_address")
 */
class CheckMailingAddress extends Base
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullAddress();
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        $address = [];
        if ($address1 = $this->getAddress1()) {
            $address[] = $address1;
        }
        if ($address2 = $this->getAddress2()) {
            $address[] = $address2;
        }

        return sprintf('%s, %s, %s %s', implode(' ', $address), $this->getCity(), $this->getState(), $this->getZip());
    }
}
