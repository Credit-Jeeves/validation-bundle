<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Address as Base;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Address
 *
 * @ORM\Table(name="cj_address")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\AddressRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Address extends Base
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
    public function getAddress()
    {
        $address = [];
        $result = [];
        if ($number = $this->getNumber()) {
            $address[] = $number;
        }
        if ($street = $this->getStreet()) {
            $address[] = $street;
        }

        if ($address) {
            $result[] = implode(' ', $address);
        }

        if ($district = $this->getDistrict()) {
            $result[] = $district;
        }

        return implode(', ', $result);
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        $address = [];
        $result = [];
        if ($number = $this->getNumber()) {
            $address[] = $number;
        }
        if ($street = $this->getStreet()) {
            $address[] = $street;
        }
        if ($address) {
            $result[] = implode(' ', $address);
        }
        if ($district = $this->getDistrict()) {
            $result[] = $district;
        }
        if ($city = $this->getCity()) {
            $result[] = $city;
        }
        if ($area = $this->getArea()) {
            $result[] = $area;
        }

        return implode(', ', $result) . ' ' . $this->getZip();
    }
}
