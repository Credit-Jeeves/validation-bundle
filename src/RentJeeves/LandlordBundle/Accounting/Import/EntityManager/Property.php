<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use CreditJeeves\DataBundle\Entity\Group;
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
    protected function catchMatchedExternalPropertyId(PropertyMapping $propertyMapping, $externalPropertyId)
    {
        if ($propertyMapping->getExternalPropertyId() !== $externalPropertyId) {
            throw new ImportHandlerException(
                sprintf(
                    'Given external property mapping (%s) does not match the existing one (%s)',
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

        $externalPropertyId = $row[Mapping::KEY_EXTERNAL_PROPERTY_ID];
        $property = $currentImportModel->getContract()->getProperty();

        if (empty($property)) {
            return;
        }

        $propertyId = $property->getId();
        if (!empty($propertyId) && !empty($externalPropertyId)) {
            $keyOfPropertyMapping = sprintf('%s_%s', $property->getId(), $externalPropertyId);
        }

        if (isset($keyOfPropertyMapping) && isset($this->propertyMappingList[$keyOfPropertyMapping])) {
            $this->currentImportModel->setPropertyMapping(
                $this->propertyMappingList[$keyOfPropertyMapping]
            );

            return;
        }

        $holding = $currentImportModel->getContract()->getGroup()->getHolding();

        if ($property->getId()) {
            /** @var PropertyMapping $propertyMapping */
            $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
                [
                    'property' => $property,
                    'holding' => $holding
                ]
            );

            if ($propertyMapping) {
                $this->catchMatchedExternalPropertyId($propertyMapping, $row[Mapping::KEY_EXTERNAL_PROPERTY_ID]);

                return;
            }
        }

        $propertyMapping = new PropertyMapping();
        $propertyMapping->setProperty($property);
        $propertyMapping->setExternalPropertyId($row[Mapping::KEY_EXTERNAL_PROPERTY_ID]);
        $propertyMapping->setHolding($holding);

        if (isset($keyOfPropertyMapping)) {
            $this->propertyMappingList[$keyOfPropertyMapping] = $propertyMapping;
        }

        $this->currentImportModel->setPropertyMapping($propertyMapping);
    }

    /**
     * @param Group $group
     * @param string $externalPropertyId
     * @return bool|EntityProperty
     */
    protected function getPropertyByExternalPropertyId(Group $group, $externalPropertyId)
    {
        if ($this->storage->isMultipleProperty()) {
            return false;
        }

        $this->logger->debug(
            sprintf(
                'Looking up property by external property id: %s',
                $externalPropertyId
            )
        );
        /** @var PropertyMapping $propertyMapping */
        $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
            [
                'holding'            => $group->getHolding(),
                'externalPropertyId' => $externalPropertyId
            ]
        );

        if ($propertyMapping) {
            return $propertyMapping->getProperty();
        }

        return false;
    }

    /**
     * @return EntityProperty|null
     */
    protected function getProperty($row)
    {
        if (!$this->storage->isMultipleProperty()) {
            $propertyId = $this->storage->getPropertyId();
            if (!empty($row[Mapping::KEY_PROPERTY_ID])) {
                $propertyId = $row[Mapping::KEY_PROPERTY_ID];
            }
            $this->logger->debug(sprintf('Looking up multi-property by id: %s', $propertyId));

            return $this->em->getRepository('RjDataBundle:Property')->find($propertyId);
        }

        $group = $this->getGroup($row);

        if (!empty($row[Mapping::KEY_EXTERNAL_PROPERTY_ID]) &&
            $group &&
            $property = $this->getPropertyByExternalPropertyId($group, $row[Mapping::KEY_EXTERNAL_PROPERTY_ID])
        ) {
            return $property;
        }

        if (isset($row[Mapping::KEY_UNIT_ID]) && !empty($row[Mapping::KEY_UNIT_ID]) && $group) {
            $this->logger->debug(sprintf('Looking up property by unit_id: %s', $row[Mapping::KEY_UNIT_ID]));

            /** @var UnitMapping $mapping */
            $mapping = $this->em->getRepository('RjDataBundle:UnitMapping')->getMappingForImport(
                $group,
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
