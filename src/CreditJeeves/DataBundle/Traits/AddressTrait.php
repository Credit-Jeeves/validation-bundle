<?php
namespace CreditJeeves\DataBundle\Traits;

/**
 * @TODO: remove
 */
trait AddressTrait
{
    public function getAddress()
    {
        $address = array();
        $result = array();
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

    public function getFullAddress()
    {
        $address = array();
        $result = array();
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

        return implode(', ', $result).' '.$this->getZip();
    }
}
