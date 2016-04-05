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
    public function getFullAddress()
    {
        $address = $this->getAddress1();
        if (false === empty($this->getAddress2())) {
            $address .= ' ' . $this->getAddress2();
        }

        return sprintf('%s, %s, %s %s', $address, $this->getCity(), $this->getState(), $this->getZip());
    }
}
