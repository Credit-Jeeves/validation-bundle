<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Property as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Traits\AddressTrait;
use Geocoder\Result\Geocoded;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ComponentBundle\Utility\ShorteningAddressUtility;

/**
 * Property
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PropertyRepository")
 * @ORM\Table(name="rj_property")
 *
 */
class Property extends Base
{
    use AddressTrait {
        getFullAddress as fullAddress;
    }

    public function getShrinkAddress()
    {
        return ShorteningAddressUtility::shrinkAddress($this->getFullAddress());
    }

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
            if (empty($property['city']) && !empty($property['district'])) {
                $property['city'] = $property['district'];
                unset($property['district']);
            }
        }
        return $property;
    }

    public function parseGeocodeResponse(Geocoded $response)
    {
        $this->setLatitude($response->getLatitude());
        $this->setLongtitude($response->getLongitude());
        $this->setZip($response->getZipcode());
        $this->setArea($response->getRegionCode());
        $this->setCountry($response->getCountryCode());
        $this->setCity($response->getCity());
        if (!$this->getCity()) {
            $this->setCity($response->getCityDistrict());
        }
        $this->setNumber($response->getStreetNumber());
        $this->setStreet($response->getStreetName());
        $this->setDistrict($response->getCityDistrict());
        return $this;
    }

    public function setLatitude($data)
    {
        return $this->setJb($data);
    }

    public function setLongtitude($data)
    {
        return $this->setKb($data);
    }

    public function getLatitude()
    {
        return $this->getJb();
    }

    public function getLongtitude()
    {
        return $this->getKb();
    }
    /**
     *  jb = latitude, kb = longitude
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function parseGoogleLocation($data)
    {
        if (isset($data['geometry']['location'])) {
            $location = $data['geometry']['location'];

            if (count($location) == 2) {
                $property['jb'] = reset($location);
                $property['kb'] = end($location);
            } else {
                throw new \Exception("Unknown location from google", 1);
            }
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

    public function getItem($group = null)
    {
        $item = array();
        $item['id'] = $this->getId();
        $item['zip'] = $this->getZip();
        $item['country'] = $this->getCountry();
        $item['area'] = $this->getArea();
        $item['city'] = $this->getCity();
        $item['address'] = $this->getAddress();
        $item['isSingle'] = $this->isSingle();
        if ($group) {
            $item['units'] = $this->countUnitsByGroup($group);
        } else {
            $item['units'] = $this->getUnits()->count();
        }
        return $item;
    }

    public function countUnitsByGroup($group)
    {
        $result = 0;
        $units = $this->getUnits();
        foreach ($units as $unit) {
            if ($group == $unit->getGroup()) {
                $result++;
            }
        }
        return $result;
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

    public function searchUnit($unitSearch)
    {
        $result = null;
        foreach ($this->getUnits() as $unit) {
            if ($unitSearch === $unit->getName()) {
                $result = $unit;
                break;
            }
        }

        return $result;
    }

    public function getLocationAddress()
    {
        $result = array();
        if ($city = $this->getCity()) {
            $result[] = $city;
        }
        if ($area = $this->getArea()) {
            $result[] = $area;
        }
        return implode(', ', $result).' '.$this->getZip();
    }

    public function hasLandlord()
    {
        if ($this->getPropertyGroups()->count() <= 0) {
            return false;
        }

        $groups = $this->getPropertyGroups();
        $merchantExist = false;
        foreach ($groups as $group) {
            if (($depositAccount = $group->getDepositAccount()) && $depositAccount->isComplete()) {
                $merchantExist = true;
                break;
            }
        }

        return $merchantExist;
    }

    public function hasUnits()
    {
        if ($this->getUnits()->count() > 0) {
            return true;
        }

        return false;
    }

    public function hasGroups()
    {
        if ($this->getPropertyGroups()->count() > 0) {
            return true;
        }

        return false;
    }

    public function isSingle()
    {
        return $this->getIsSingle() == true;
    }

    public function getSingleUnit()
    {
        if ($this->isSingle()) {
            return $this->getUnits()->first();
        }

        return null;
    }

    public function hasIntegratedGroup()
    {
        foreach ($this->getPropertyGroups() as $group) {
            if ($group->getGroupSettings()->getIsIntegrated()) {
                return true;
            }
        }

        return false;
    }

    public function isAllowedToBeSingle($isSingle, $groupId)
    {
        if ($isSingle == $this->getIsSingle()) {
            return true;
        }

        // it means previously we had null or false
        if ($isSingle == true) {
            if ((!$this->hasUnits() && !$this->hasGroups()) ||
                (!$this->hasUnits() && count($this->getPropertyGroups()) == 1
                    && $this->getPropertyGroups()->first()->getId() == $groupId)) {
                return true;
            }
        }

        // isSingle = false is allowed only if previous value was null (restricted to convert standalone property)
        if ($isSingle == false) {
            if (is_null($this->getIsSingle())) {
                return true;
            }
        }


        return false;
    }

    public function __toString()
    {
        return $this->getFullAddress();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("full_address")
     * @Serializer\Groups({"RentJeevesImport", "AdminProperty"})
     */
    public function getFullAddress()
    {
        return $this->fullAddress();
    }
}
