<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Property as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ContractStatus;

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

    public function getItem($group = null)
    {
        $item = array();
        $item['id'] = $this->getId();
        $item['zip'] = $this->getZip();
        $item['country'] = $this->getCountry();
        $item['area'] = $this->getArea();
        $item['city'] = $this->getCity();
        $item['address'] = $this->getAddress();
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

//         if ($area = $this->getArea()) {
//             $result[] = $area;
//         }
//         if ($zip = $this->getZip()) {
//             $result[] = $zip;
//         }
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

    /**
     * 
     * @param string $search
     */
    public function createContract($em, $tenant, $search = null)
    {
        $units = $this->getUnits();
        foreach ($units as $unit) {
            if ($search) {
                if ($search == $unit->getName()) {
                    
                }
            } else {
                $contract = new Contract();
                $contract->setTenant($tenant);
                $contract->setHolding($unit->getHolding());
                $contract->setGroup($unit->getGroup());
                $contract->setProperty($unit->getProperty());
                $contract->setStatus(ContractStatus::PENDING);
                //$contract->setSearch($search)
                $em->persist($contract);
                //echo $unit->getName();
            }
            $em->flush();
        }
        //echo __METHOD__.$tenant->getFirstName();
    }
}
