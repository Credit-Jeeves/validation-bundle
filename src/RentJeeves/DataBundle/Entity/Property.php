<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\DataBundle\Model\Property as Base;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ComponentBundle\Utility\ShorteningAddressUtility;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PropertyRepository")
 * @ORM\Table(name="rj_property")
 */
class Property extends Base
{
    /**
     * This method is needed only for SonataAdminBundle, please don't use it.
     * @return bool
     */
    public function getIsMultipleBuildings()
    {
        return $this->isMultipleBuildings();
    }

    public function getShrinkAddress($length = ShorteningAddressUtility::MAX_LENGTH)
    {
        return ShorteningAddressUtility::shrinkAddress($this->getFullAddress(), $length);
    }

    /**
     * @deprecated PLS DONT USE IT, Use normalizer or something like normalizer
     *
     * @todo NEED REMOVE
     * @return array
     */
    public function getItem($group = null)
    {
        if ($this->getPropertyAddress() === null) {
            throw new \LogicException('U can use this function only for exist property');
        }

        $item['id'] = $this->getId();
        $item['zip'] = $this->getPropertyAddress()->getZip();
        $item['country'] = '';
        $item['area'] = $this->getPropertyAddress()->getState();
        $item['city'] = $this->getPropertyAddress()->getCity();
        $item['address'] = $this->getPropertyAddress()->getAddress();
        $item['isSingle'] = $this->getPropertyAddress()->isSingle();
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

    public function hasLandlord()
    {
        if ($this->getPropertyGroups()->count() <= 0) {
            return false;
        }

        $groups = $this->getPropertyGroups();
        $merchantExist = false;
        /** @var Group $group */
        foreach ($groups as $group) {
            if (($depositAccount = $group->getRentDepositAccountForCurrentPaymentProcessor()) &&
                $depositAccount->isComplete()
            ) {
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

    /**
     * @deprecated pls use `$property->getPropertyAddress()->isSingle()`
     *
     * @return bool
     */
    public function isSingle()
    {
        return $this->getPropertyAddress()->isSingle();
    }

    /**
     * @return mixed|null
     */
    public function getExistingSingleUnit()
    {
        if ($this->getPropertyAddress()->isSingle() === true) {
            $unit = $this->getUnits()->first();
            if (!$unit) {
                throw new \LogicException(
                    sprintf(
                        'Standalone property "%s" with id "%s" has no unit.',
                        $this->getPropertyAddress()->getAddress(),
                        $this->getId()
                    )
                );
            }

            return $unit;
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

    public function isAllowedToSetSingle($isSingle, $groupId)
    {
        if ($isSingle == $this->getPropertyAddress()->isSingle()) {
            return true;
        }

        // it means previously we had null or false
        if ($isSingle == true) {
            if ((!$this->hasUnits() && !$this->hasGroups()) ||
                (!$this->hasUnits() && count($this->getPropertyGroups()) == 1
                    && $this->getPropertyGroups()->first()->getId() == $groupId)
            ) {
                return true;
            }
        }

        // isSingle = false is allowed only if previous value was null (restricted to convert standalone property)
        if ($isSingle == false) {
            if (is_null($this->getPropertyAddress()->isSingle())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPropertyAddress()->getFullAddress();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("full_address")
     * @Serializer\Groups({"RentJeevesImport", "AdminProperty"})
     */
    public function getFullAddress()
    {
        return $this->getPropertyAddress()->getFullAddress();
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function hasGroup(Group $group)
    {
        return !!$this->getPropertyGroups()->filter(function (Group $entity) use ($group) {
            return $entity->getId() === $group->getId();
        });
    }

    /**
     * @deprecated pls use `->getPropertyAddress()->getAddress()`
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getPropertyAddress()->getAddress();
    }

    /**
     * @param Holding $holding
     *
     * @return PropertyMapping
     */
    public function getPropertyMappingByHolding(Holding $holding)
    {
        /** @var $propertyMapping PropertyMapping */
        foreach ($this->getPropertyMapping() as $propertyMapping) {
            if ($propertyMapping->getHolding()->getId() === $holding->getId()) {
                return $propertyMapping;
            }
        }

        return null;
    }
}
