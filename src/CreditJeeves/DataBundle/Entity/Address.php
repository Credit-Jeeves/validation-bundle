<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Address as Base;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\DataBundle\Traits\AddressTrait;

/**
 * Address
 *
 * @ORM\Table(name="cj_address")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\AddressRepository")
 */
class Address extends Base
{

    use AddressTrait;

    public function __toString()
    {
        return $this->getFullAddress();
    }
}
