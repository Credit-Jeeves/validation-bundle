<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\DataBundle\Entity\Unit as EntityUnit;
use CreditJeeves\DataBundle\Entity\Group as EntityGroup;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageInterface;

/**
 * @property EntityManager $em
 * @property EntityGroup group
 * @property StorageInterface $storage
 */
trait Unit
{
    protected $externalUnitIdList = [];

    protected $unitList = [];

    /**
     * @param $row
     *
     * @return EntityUnit
     */
    protected function getUnit(array $row, EntityProperty $property = null)
    {
        if (is_null($property)) {
            return null;
        }

        // existing single property, so simply return it's single unit
        if ($property->isSingle()) {
            return $property->getExistingSingleUnit();
        }

        // all units should have group and holding set
        if ($this->group) {
            $params['group'] = $this->group->getId();
            !$this->group->getHolding() || $params['holding'] = $this->group->getHolding()->getId();
        }

        // unit name is empty -- treat as a new single property
        $unitId = (isset($row[Mapping::KEY_UNIT_ID]))? $row[Mapping::KEY_UNIT_ID] : '';
        $unitName = $row[Mapping::KEY_UNIT];
        if ($this->isEmptyString($unitName) && !$this->isEmptyString($unitId)) {
            $this->logger->debug("Unit name is empty, but has unit id (" . $unitId . ")");
            $property->addPropertyGroup($this->group);
            $this->propertyProcess->setupSingleProperty($property);
            return $property->getUnits()->first();
        }

        /*
         * find unit within multi-unit property...
         */
        $params = array(
            'name' => $row[Mapping::KEY_UNIT],
        );

        if ($this->storage->isMultipleProperty() && !is_null($property)) {
            $params['property'] = $property->getId();
        } elseif ($this->storage->getPropertyId()) {
            $params['property'] = $this->storage->getPropertyId();
        }

        if (!empty($params['name']) && !empty($params['property'])) {
            $unit = $this->em->getRepository('RjDataBundle:Unit')->findOneBy($params);
        }

        if (!empty($unit)) {
            return $unit;
        }

        /*
         * ...or create a new one.
         */
        $key = '';
        foreach ($params as $param) {
            $key .= $param."_";
        }

        if (array_key_exists($key, $this->unitList)) {
            return $this->unitList[$key];
        }

        $unit = new EntityUnit();
        $unit->setName($unitName);
        if ($property) {
            $unit->setProperty($property);
        }
        if ($this->group) {
            $unit->setGroup($this->group);
            $unit->setHolding($this->group->getHolding());
        }

        $this->unitList[$key] = $unit;

        return $unit;
    }

    /**
     * @param array $row
     * @return UnitMapping
     */
    public function getUnitMapping(array $row, EntityUnit $unit)
    {
        if (!$unit) {
            throw new InvalidArgumentException('The unit argument cannot be null.');
        }

        if (!array_key_exists(Mapping::KEY_UNIT_ID, $row)) {
            return new UnitMapping();
        }

        $externalUnitId = $row[Mapping::KEY_UNIT_ID];

        if (array_key_exists($externalUnitId, $this->externalUnitIdList)) {
            return $this->externalUnitIdList[$externalUnitId];
        }

        if (!$this->storage->isMultipleProperty()) {
            $unitMapping = new UnitMapping();
            $this->externalUnitIdList[$externalUnitId] = $unitMapping;
            return $unitMapping;
        }

        $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            array(
                'externalUnitId' => $externalUnitId,
                'unit' => $unit
            )
        );
        if (empty($unitMapping)) {
            $unitMapping = new UnitMapping();
            $unitMapping->setExternalUnitId($externalUnitId);
        }

        $this->externalUnitIdList[$externalUnitId] = $unitMapping;

        return $unitMapping;
    }

    protected function isEmptyString($str)
    {
        return (empty($str) && $str !== '0');
    }
}
