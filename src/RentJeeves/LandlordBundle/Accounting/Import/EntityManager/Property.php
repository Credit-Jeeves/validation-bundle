<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
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

        if (isset($row[Mapping::KEY_UNIT_ID]) && !empty($row[Mapping::KEY_UNIT_ID]) && $this->group) {
            /** @var UnitMapping $mapping */
            $mapping = $this->em->getRepository('RjDataBundle:UnitMapping')->getMappingForImport(
                $this->group,
                $row[Mapping::KEY_UNIT_ID]
            );

            $property = !empty($mapping) ? $mapping->getUnit()->getProperty() : null;
        }

        if (!empty($property)) {
            return $property;
        }

        /** @var $property EntityProperty */
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

        if (!$this->propertyProcess->isValidProperty(
            $property
        )) {
            return null;
        }

        $property = $this->propertyProcess->checkPropertyDuplicate(
            $property,
            $saveToGoogle = true
        );

        /** Save valid property to DB */
        $this->em->flush($property);

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
            /** @var $unit Unit */
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
