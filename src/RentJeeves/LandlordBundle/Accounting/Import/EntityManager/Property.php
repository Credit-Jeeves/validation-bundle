<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

trait Property
{
    protected $propertyList = array();

    /**
     * @return EntityProperty|null
     */
    protected function getProperty($row)
    {
        if (!$this->storage->isMultipleProperty()) {
            return $this->em->getRepository('RjDataBundle:Property')->find($this->storage->getPropertyId());
        }

        /**
         * @var $property EntityProperty
         */
        $property =  $this->mapping->createProperty($row);
        if ($propertyByUnit = $this->tryMapPropertyByUnit(
            $property,
            $row[Mapping::KEY_UNIT],
            $row[Mapping::KEY_UNIT_ID]
        )) {
            return $propertyByUnit;
        }

        $key = md5($property->getFullAddress());
        if (array_key_exists($key, $this->propertyList)) {
            return $this->propertyList[$key];
        }

        $isValid = $this->propertyProcess->isValidProperty(
            $property
        );

        if (!$isValid) {
            return null;
        }
        $property = $this->propertyProcess->checkPropertyDuplicate(
            $property,
            $saveToGoogle = true
        );

        $this->propertyList[$key] = $property;

        return $property;
    }

    /**
     * @param EntityProperty $property
     * @param $unitName
     * @param $unitId
     *
     * @return null|Property
     */
    protected function tryMapPropertyByUnit(EntityProperty $property, $unitName, $unitId)
    {
        if ($this->group) {
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
        }

        return null;
    }
}
