<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;

trait ImportUnit
{
    /**
     * @param $row
     *
     * @return Unit
     */
    protected function getUnit(array $row, Property $property = null)
    {
        if (is_null($property)) {
            return null;
        }

        if ($property->isSingle()) {
            return $property->getSingleUnit();
        }

        if (empty($row[ImportMapping::KEY_UNIT])) {
            return null;
        }

        $params = array(
            'name' => $row[ImportMapping::KEY_UNIT],
        );

        if ($this->group) {
            $params['group'] = $this->group;
        }

        if ($this->storage->isMultipleProperty() && !is_null($property)) {
            $params['property'] = $property->getId();
        } elseif ($this->storage->getPropertyId()) {
            $params['property'] = $this->storage->getPropertyId();
        }

        if ($holding = $this->group->getHolding()) {
            $params['holding'] = $holding;
        }

        if (!empty($params['name']) && !empty($params['property'])) {
            $unit = $this->em->getRepository('RjDataBundle:Unit')->findOneBy($params);
        }

        if (!empty($unit)) {
            return $unit;
        }

        $unit = new Unit();
        $unit->setName($row[ImportMapping::KEY_UNIT]);
        if ($property) {
            $unit->setProperty($property);
        }
        $unit->setHolding($this->group->getHolding());
        $unit->setGroup($this->group);

        return $unit;
    }

    /**
     * @param array $row
     * @return UnitMapping
     */
    public function getUnitMapping(array $row)
    {
        if (!$this->storage->isMultipleProperty()) {
            return new UnitMapping();
        }

        $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            array(
                'externalUnitId' => $row[ImportMapping::KEY_UNIT_ID],
            )
        );
        if (!$unitMapping) {
            $unitMapping = new UnitMapping();
            $unitMapping->setExternalUnitId($row[ImportMapping::KEY_UNIT_ID]);
        }

        return $unitMapping;
    }
}
