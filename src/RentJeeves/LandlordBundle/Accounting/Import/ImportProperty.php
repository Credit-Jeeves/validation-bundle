<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;


use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Unit;

trait ImportProperty
{
    /**
     * @return Property|null
     */
    protected function getProperty($row)
    {
        if (!$this->storage->isMultipleProperty()) {
            return $this->em->getRepository('RjDataBundle:Property')->find($this->storage->getPropertyId());
        }

        $isValid = $this->propertyProcess->isValidProperty(
            $property =  $this->mapping->createProperty($row)
        );

        if (!$isValid) {
            return $this->tryMapPropertyByUnit(
                $row[ImportMapping::KEY_UNIT],
                $row[ImportMapping::KEY_UNIT_ID]
            );
        }

        return $this->propertyProcess->checkPropertyDuplicate(
            $property,
            $saveToGoogle = true
        );
    }

    /**
     * @param $unitName
     * @param $unitId
     * @return null|\RentJeeves\DataBundle\Entity\Property
     */
    protected function tryMapPropertyByUnit($unitName, $unitId)
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
            return $unit->getProperty();
        }

        return null;
    }
}
