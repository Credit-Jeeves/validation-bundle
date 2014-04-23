<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Property as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Traits\AddressTrait;

/**
 * Property
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PropertyRepository")
 * @ORM\Table(name="rj_property")
 *
 */
class Property extends Base
{
    use AddressTrait;

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

    /**
     * @TODO this method so big, maybe we create some service for work with contract? Because seems it's wrong way.
     *
     * @param string $search
     */
    public function createContract($em, Tenant $tenant, $search = null, $contractWaiting = null)
    {
        // Search for unit
        $units = $this->getUnits();
        if ($unit = $this->searchUnit($search)) {
            $contract = new Contract();
            $contract->setTenant($tenant);
            $contract->setHolding($unit->getHolding());
            $contract->setGroup($unit->getGroup());
            $contract->setProperty($unit->getProperty());
            $contract->setStatus(ContractStatus::PENDING);
            $contract->setUnit($unit);

            /**
             * @var $contractWaiting ContractWaiting
             */
            if (!empty($contractWaiting)) {
                $contract->setStatus(ContractStatus::APPROVED);
                $contract->setStartAt($contractWaiting->getStartAt());
                $contract->setFinishAt($contractWaiting->getFinishAt());
                $contract->setImportedBalance($contractWaiting->getImportedBalance());
                $contract->setRent($contractWaiting->getRent());

                $group = $contract->getUnit()->getGroup();
                $hasResident = true;
                /**
                 * On the database level it can be null, so we must check
                 */
                if (!empty($group) && $holding = $group->getHolding()) {
                    $hasResident = $tenant->hasResident(
                        $holding,
                        $contractWaiting->getResidentId()
                    );
                }

                if (!$hasResident) {
                    $residentMapping = new ResidentMapping();
                    $residentMapping->setResidentId($contractWaiting->getResidentId());
                    $residentMapping->setHolding($holding);
                    $residentMapping->setTenant($tenant);
                    $em->persist($residentMapping);
                }

                $em->remove($contractWaiting);
            }

            $em->persist($contract);
            $em->flush();
            return true;
        }

        // If there is no such unit we'll send contract for all potential landlords
        $groups = $this->getPropertyGroups();
        foreach ($groups as $group) {
            $contract = new Contract();
            $contract->setTenant($tenant);
            $contract->setHolding($group->getHolding());
            $contract->setGroup($group);
            $contract->setProperty($this);
            $contract->setStatus(ContractStatus::PENDING);
            $contract->setSearch($search);
            $em->persist($contract);
        }

        $em->flush();
        return true;
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

    public function __toString()
    {
        return $this->getFullAddress();
    }
}
