<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;


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
            return null;
        }

        return $this->propertyProcess->checkPropertyDuplicate(
            $property,
            $saveToGoogle = true
        );
    }
}
