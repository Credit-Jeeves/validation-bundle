<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;


use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;

trait ImportProperty
{
    protected $propertyList = array();

    /**
     * @return Property|null
     */
    protected function getProperty($row)
    {
        if (!$this->storage->isMultipleProperty()) {
            return $this->em->getRepository('RjDataBundle:Property')->find($this->storage->getPropertyId());
        }
        /**
         * @var $property Property
         */
        $property =  $this->mapping->createProperty($row);
        $key = md5($property->getFullAddress());

        if (array_key_exists($key, $this->propertyList)) {
            return $this->propertyList[$key];
        }

        $isValid = $this->propertyProcess->isValidProperty(
            $property
        );

        if (!$isValid) {
            return $this->tryMapPropertyByUnit(
                $property,
                $row[ImportMapping::KEY_UNIT],
                $row[ImportMapping::KEY_UNIT_ID]
            );
        }
        $property = $this->propertyProcess->checkPropertyDuplicate(
            $property,
            $saveToGoogle = true
        );

        $this->propertyList[$key] = $property;

        return $property;
    }

    /**
     * @param $unitName
     * @param $unitId
     * @return null|\RentJeeves\DataBundle\Entity\Property
     */
    protected function tryMapPropertyByUnit(Property $property, $unitName, $unitId)
    {
        /**
         * @var $unit Unit
         */
        $unit = $this->em->getRepository('RjDataBundle:Unit')
            ->getImportUnit(
                $this->group->getId(),
                $unitName,
                $unitId
            );
        if ($unit) {
            $this->propertyList[md5($property->getFullAddress())] = $unit->getProperty();
            return $unit->getProperty();
        }

        return null;
    }
}
