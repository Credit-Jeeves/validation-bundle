<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Property as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * Property
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PropertyRepository")
 * @ORM\Table(name="rj_property")
 *
 */
class Property extends Base
{
    public function parseGoogleAddress($data)
    {
        $property = array();
        if (isset($data['address'])) {
            $address = $data['address'];
            foreach ($address as $details) {
                if (isset($details['types'])) {
                    $types = $details['types'];
                    if (in_array('postal_code', $types)) {
                        $property['zip'] = $details['long_name'];
                    }
                    if (in_array('country', $types)) {
                        $property['country'] = $details['short_name'];
                    }
                    if (in_array('administrative_area_level_1', $types)) {
                        $property['area'] = $details['short_name'];
                    }
                    if (in_array('locality', $types)) {
                        $property['city'] = $details['long_name'];
                    }
                    if (in_array('sublocality', $types)) {
                        $property['district'] = $details['long_name'];
                    }
                    if (in_array('route', $types)) {
                        $property['street'] = $details['long_name'];
                    }
                    if (in_array('street_number', $types)) {
                        $property['number'] = $details['long_name'];
                    }
                }
            }
        }
        return $property;
    }

    public function parseGoogleLocation($data)
    {
        if (isset($data['geometry']['location'])) {
            $location = $data['geometry']['location'];
            $property['jb'] = $location['jb'];
            $property['kb'] = $location['kb'];
        }
        return $property;
    }

    public function fillPropertyData(array $details)
    {
        foreach ($details as $key => $value) {
            $this->{$key} = $value;
        }
        return $this;
    }

    public function getItem()
    {
        $item = array();
        $item['id'] = $this->getId();
        $item['country'] = $this->getCountry();
        $item['area'] = $this->getArea();
        $item['city'] = $this->getCity();
        $item['address'] = $this->getAddress();
        $item['units'] = $this->getUnits()->count();
        return $item;
    }

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

        if ($city = $this->getCity()) {
            $result[] = $city;
        }

        if ($area = $this->getArea()) {
            $result[] = $area;
        }
        if ($zip = $this->getZip()) {
            $result[] = $zip;
        }
        return implode(', ', $result);
    }

    public function getUnitsArray()
    {
        $result = array();
        $units = $this->getUnits();
        foreach ($units as $unit) {
            $item = array();
            $item['id'] = $unit->getId();
            $item['name'] = $unit->getName();
            $result[] = $item;
        }
        return $result;
    }
}
