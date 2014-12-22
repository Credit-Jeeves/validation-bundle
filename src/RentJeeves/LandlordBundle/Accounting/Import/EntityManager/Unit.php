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
    protected $externalUnitIdList = array();

    protected $unitList = array();

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

        if ($property->isSingle()) {
            return $property->getSingleUnit();
        }

        if (empty($row[Mapping::KEY_UNIT]) && !empty($row[Mapping::KEY_UNIT_ID])) {
            /**
             * @var $unitMapping UnitMapping
             */
            $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
                array('externalUnitId' => $row[Mapping::KEY_UNIT_ID])
            );
            if ($unitMapping) {
                return $unitMapping->getUnit();
            } elseif ($property->getIsSingle() === null && !empty($row[Mapping::KEY_UNIT_ID])) {
                $unit = EntityProperty::getNewSingleUnit($property);
                $property->setIsSingle(true);
                return $unit;
            }
        }

        $params = array(
            'name' => $row[Mapping::KEY_UNIT],
        );

        if ($this->group) {
            $params['group'] = $this->group->getId();
            !$this->group->getHolding() || $params['holding'] = $this->group->getHolding()->getId();
        }

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
        $key = '';
        foreach ($params as $param) {
            $key .= $param."_";
        }

        if (array_key_exists($key, $this->unitList)) {
            return $this->unitList[$key];
        }

        $unit = new EntityUnit();
        $unit->setName($row[Mapping::KEY_UNIT]);
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
    public function getUnitMapping(array $row)
    {
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
            )
        );
        if (empty($unitMapping)) {
            $unitMapping = new UnitMapping();
            $unitMapping->setExternalUnitId($externalUnitId);
        }

        $this->externalUnitIdList[$externalUnitId] = $unitMapping;

        return $unitMapping;
    }
}
