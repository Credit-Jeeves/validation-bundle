<?php

namespace RentJeeves\ImportBundle\PropertyImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\NonUniqueResultException;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\Exception\ImportRuntimeException;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Service`s name "import.property.loader.unmapped"
 */
class UnmappedLoader extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    protected function preCheckData(Import $import, $externalPropertyId)
    {
    }

    /**
     * @param ImportProperty $importProperty
     * @return Property
     * @throws ImportInvalidArgumentException
     * @throws ImportLogicException
     */
    protected function processProperty(ImportProperty $importProperty)
    {
        $group = $importProperty->getImport()->getGroup();

        $property = $this->propertyManager->getOrCreatePropertyByAddressFields(
            $importProperty->getAddress1(),
            null,
            $importProperty->getCity(),
            $importProperty->getState(),
            $importProperty->getZip(),
            $importProperty->getUnitName()
        );

        if (null === $property) { // ImportProperty has incorrect address
            $this->logger->alert(
                $message = sprintf(
                    'Address is invalid for ImportProperty#%d',
                    $importProperty->getId()
                ),
                [
                    'group' => $importProperty->getImport()->getGroup()
                ]
            );

            throw new ImportInvalidArgumentException($message);
        }

        if (true === $this->isDifferentPropertyShouldBeCreated($property, $group)) {
            $propertyAddress = $property->getPropertyAddress();
            $property = new Property();
            $property->setPropertyAddress($propertyAddress);
        }

        if ($property->getPropertyGroups()->isEmpty()) {
            $property->addPropertyGroup($group);
            $group->addGroupProperty($property);
        }

        if ($importProperty->isAddressHasUnits()) {
            $this->propertyManager->setupMultiUnitProperty($property);
        } else {
            try {
                $this->propertyManager->setupSingleProperty($property, ['doFlush' => false]);
            } catch (\RuntimeException $e) {
                $this->logger->warning(
                    $e->getMessage(),
                    [
                        'group' => $importProperty->getImport()->getGroup()
                    ]
                );
                throw new ImportLogicException($e->getMessage());
            }
        }

        $property->setIsMultipleBuildings($importProperty->isPropertyHasBuildings());

        return $property;
    }

    /**
     * @param Property       $property
     * @param ImportProperty $importProperty
     * @return Unit
     * @throws ImportLogicException
     * @throws ImportRuntimeException
     * @throws ImportInvalidArgumentException
     */
    protected function processUnit(Property $property, ImportProperty $importProperty)
    {
        try {
            if (!$importProperty->getExternalUnitId()) {
                $this->logger->warning(
                    $message = 'ExternalUnitId is required field',
                    [
                        'group' => $importProperty->getImport()->getGroup(),
                    ]
                );
                throw new ImportInvalidArgumentException($message);
            }
            $group = $importProperty->getImport()->getGroup();
            $unitMapping = $this->em
                ->getRepository('RjDataBundle:UnitMapping')
                ->getMappingForImport($group, $importProperty->getExternalUnitId());

            if ($unitMapping &&
                (!$property->getId() || $unitMapping->getUnit()->getProperty()->getId() != $property->getId())
            ) {
                $this->logger->warning(
                    $message = sprintf(
                        'Unit#%d found by external unit id and group but do not belong to processing property',
                        $unitMapping->getUnit()->getId()
                    ),
                    [
                        'group' => $importProperty->getImport()->getGroup(),
                    ]
                );
                throw new ImportLogicException($message);
            }

            if ($unitMapping) {
                $unit = $unitMapping->getUnit();
            } elseif ($property->isSingle()) {
                $unit = $property->getExistingSingleUnit();
                if (!$unit) {
                    $this->logger->warning(
                        $message = sprintf(
                            'Single unit should be created for single Property#%d',
                            $property->getId()
                        ),
                        [
                            'group' => $importProperty->getImport()->getGroup(),
                            'additional_parameter' => $importProperty->getExternalPropertyId()
                        ]
                    );
                    throw new ImportLogicException($message);
                }
            } elseif (!$unitMapping && $property->hasUnits()) {
                $unit = $this->em
                    ->getRepository('RjDataBundle:Unit')
                    ->getImportUnitByPropertyGroupAndUnitName($property, $group, $importProperty->getUnitName());
                if ($unit && $unit->getUnitMapping()) {
                    $this->logger->warning(
                        $message = sprintf(
                            'Unit#%d found by group and name should have corresponding mapping',
                            $unit->getId()
                        ),
                        [
                            'group' => $importProperty->getImport()->getGroup(),
                        ]
                    );
                    throw new ImportLogicException($message);
                }
            }

            if (empty($unit)) {
                $unit = new Unit();
                $unit->setProperty($property);
                $unit->setHolding($group->getHolding());
                $unit->setGroup($group);
            }

            if (!$property->isSingle()) {
                // @cary: Our new import 2.0 assumptions are that
                // "The Accounting System (or CSV) is the Source of Truth".
                //  So if the unit name is different from the A.S., then we should update it in our DB.
                $unit->setName($importProperty->getUnitName());
            }

            if (!$unit->getUnitMapping()) {
                $unitMapping = new UnitMapping();
                $unitMapping->setUnit($unit);
                $unitMapping->setExternalUnitId($importProperty->getExternalUnitId());
                $unit->setUnitMapping($unitMapping);
            }

            $this->validateUnit($unit);

            $this->em->persist($unit);
            $this->em->persist($unitMapping);

            return $unit;
        } catch (NonUniqueResultException $e) {
            throw new ImportRuntimeException('Try to find unit but get non unique result');
        }
    }

    /**
     * @param Property       $property
     * @param Group          $group
     *
     * @return bool true if we need create new Property
     */
    protected function isDifferentPropertyShouldBeCreated(Property $property, Group $group)
    {
        if ($property->getId() === null) {
            return false;
        }

        // found property  but belongs to a different group -- so we need a different property record.
        if (!$property->getPropertyGroups()->isEmpty() && !$property->getPropertyGroups()->contains($group)) {
            return true;
        }

        return false;
    }

    /**
     * @param Unit $unit
     *
     * @throws ImportLogicException if Unit is not valid
     */
    protected function validateUnit(Unit $unit)
    {
        $unitErrors = $this->validator->validate($unit, ['import']);
        $errors = [];
        /** @var ConstraintViolation $constraint */
        foreach ($unitErrors as $constraint) {
            $errors[] = sprintf('%s : %s', $constraint->getPropertyPath(), $constraint->getMessage());
        }

        if (false === empty($errors)) {
            throw new ImportLogicException(
                sprintf('Unit is not valid: %s', implode(', ', array_values($errors)))
            );
        }
    }

    /**
     * Method need b/c on mapped we save property and propertyMapping but on unmapped save just property only.
     *
     * @param ImportProperty $importProperty
     * @param Property $property
     */
    protected function saveData(ImportProperty $importProperty, Property $property)
    {
        $this->em->persist($property);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportProperties(Import $import, $additionalParameter)
    {
        return $this->em
            ->getRepository('RjDataBundle:ImportProperty')
            ->getNotProcessedImportProperties($import);
    }
}
