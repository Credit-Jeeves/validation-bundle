<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Exception\ImportHandlerException;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @property Import currentImportModel
 */
trait Property
{
    /**
     * @var array
     */
    protected $propertyList = [];

    /**
     * @var array
     */
    protected $propertyMappingList = [];

    /**
     * @param PropertyMapping $propertyMapping
     * @param $externalPropertyId
     *
     * @throws ImportHandlerException
     */
    protected function isMatchExternalPropertyId(PropertyMapping $propertyMapping, $externalPropertyId)
    {
        if ($propertyMapping->getExternalPropertyId() !== $externalPropertyId) {
            throw new ImportHandlerException(
                sprintf(
                    'External property mapping is different (%s) (%s)',
                    $propertyMapping->getExternalPropertyId(),
                    $externalPropertyId
                )
            );
        }
    }

    /**
     * @param Import $currentImportModel
     * @param array  $row
     */
    protected function setPropertyMapping(Import $currentImportModel, array $row)
    {
        if (empty($row[Mapping::KEY_EXTERNAL_PROPERTY_ID])) {
            return;
        }

        $property = $currentImportModel->getContract()->getProperty();

        if (empty($property)) {
            return;
        }

        $holding = $currentImportModel->getContract()->getGroup()->getHolding();
        /** @var PropertyMapping $propertyMapping */
        $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
            [
                'property' => $property,
                'holding' => $holding
            ]
        );

        if ($propertyMapping) {
            $this->isMatchExternalPropertyId($propertyMapping, $row[Mapping::KEY_EXTERNAL_PROPERTY_ID]);

            return;
        }

        $propertyMapping = new PropertyMapping();
        $propertyMapping->setProperty($property);
        $propertyMapping->setExternalPropertyId($row[Mapping::KEY_EXTERNAL_PROPERTY_ID]);
        $propertyMapping->setHolding($holding);

        if (!array_keys($this->propertyMappingList, $propertyMapping->__toString())) {
            $this->propertyMappingList[$propertyMapping->__toString()] = $propertyMapping;
        }

        $this->currentImportModel->setPropertyMapping(
            $this->propertyMappingList[$propertyMapping->__toString()]
        );
    }

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

            $property = (!empty($mapping)) ? $mapping->getUnit()->getProperty() : null;
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
