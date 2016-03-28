<?php

namespace RentJeeves\ImportBundle\PropertyImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ImportPropertyStatus;
use RentJeeves\ImportBundle\Exception\ImportException;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\Exception\ImportRuntimeException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

/**
 * Service`s name "import.property.loader.mapped"
 */
class MappedLoader implements PropertyLoaderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PropertyManager
     */
    protected $propertyManager;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManager   $em
     * @param PropertyManager $propertyManager
     * @param Validator       $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $em,
        PropertyManager $propertyManager,
        Validator $validator,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->propertyManager = $propertyManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @param Import $import
     * @param string $externalPropertyId
     *
     * @throws ImportInvalidArgumentException
     */
    public function loadData(Import $import, $externalPropertyId = null)
    {
        if (null === $externalPropertyId) {
            throw new ImportInvalidArgumentException(
                sprintf('ExternalPropertyId cant be "null" for "%s"', __CLASS__)
            );
        }

        $this->logger->info(
            sprintf(
                'Starting process load property from Import#%d.',
                $import->getId()
            ),
            ['group' => $import->getGroup(), 'additional_parameter' => $externalPropertyId]
        );

        $iterableResult = $this->em
            ->getRepository('RjDataBundle:ImportProperty')
            ->getNotProcessedImportProperties($import, $externalPropertyId);
        /** @var ImportProperty $importProperty */
        while ((list($importProperty) = $iterableResult->next()) !== false) {
            $this->processImportProperty($importProperty);
            $this->em->flush($importProperty);
            $this->em->clear();
        }

        $this->logger->info(
            sprintf(
                'Finished process load property from Import#%d.',
                $import->getId()
            ),
            ['group' => $import->getGroup(), 'additional_parameter' => $externalPropertyId]
        );
    }

    /**
     * @param ImportProperty $importProperty
     */
    protected function processImportProperty(ImportProperty $importProperty)
    {
        $this->logger->debug(
            sprintf('Start processing ImportProperty#%d', $importProperty->getId()),
            [
                'group' => $importProperty->getImport()->getGroup(),
                'additional_parameter' => $importProperty->getExternalPropertyId()
            ]
        );

        try {
            $property = $this->processProperty($importProperty);

            if (!$property->isSingle()) {
                $unit = $this->processUnit($property, $importProperty);
            }

            if (!$property->getId()) {
                $importProperty->setStatus(ImportPropertyStatus::NEW_PROPERTY_AND_UNIT);
            } elseif (isset($unit) && !$unit->getId()) {
                $importProperty->setStatus(ImportPropertyStatus::NEW_UNIT);
            } else {
                $importProperty->setStatus(ImportPropertyStatus::MATCH);
            }

            $this->em->persist($property);
            $this->em->persist($property->getPropertyMappingByHolding($importProperty->getImport()->getGroup()->getHolding()));
            $this->em->flush();
        } catch (ImportException $e) {
            $this->logger->error(
                sprintf('%s on %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()),
                [
                    'group' => $importProperty->getImport()->getGroup(),
                    'additional_parameter' => $importProperty->getExternalPropertyId()
                ]
            );
            $importProperty->setStatus(ImportPropertyStatus::ERROR);
            $importProperty->setErrorMessages([
                $e->getMessage()
            ]);
        }

        $this->logger->debug(
            sprintf(
                'Processed ImportProperty #%d with result "%s"',
                $importProperty->getId(),
                $importProperty->getStatus()
            ),
            [
                'group' => $importProperty->getImport()->getGroup(),
                'additional_parameter' => $importProperty->getExternalPropertyId()
            ]
        );
        $importProperty->setProcessed(true);
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
        if (null === $property = $this->getPropertyByImportProperty($importProperty)) {
            $this->logger->alert(
                $message = sprintf(
                    'Address is invalid for ImportProperty#%d',
                    $importProperty->getId()
                ),
                [
                    'group' => $importProperty->getImport()->getGroup(),
                    'additional_parameter' => $importProperty->getExternalPropertyId()
                ]
            );

            throw new ImportInvalidArgumentException($message);
        }
        $this->logger->debug(
            $property->getId() === null ? 'Created new Property.' : 'Found Property#' . $property->getId(),
            [
                'group' => $importProperty->getImport()->getGroup(),
                'additional_parameter' => $importProperty->getExternalPropertyId()
            ]
        );

        if (true === $this->isDifferentPropertyShouldBeCreated($property, $group, $importProperty)) {
            $propertyAddress = $property->getPropertyAddress();
            $property = new Property();
            $property->setPropertyAddress($propertyAddress);
        }

        if ($property->getPropertyGroups()->isEmpty()) {
            $property->addPropertyGroup($group);
            $group->addGroupProperty($property);
        }

        if (null === $property->getId() ||
            null === $propertyMapping = $property->getPropertyMappingByHolding($group->getHolding())
        ) {
            $propertyMapping = new PropertyMapping();
            $propertyMapping->setExternalPropertyId($importProperty->getExternalPropertyId());
            $propertyMapping->setHolding($group->getHolding());
            $propertyMapping->setProperty($property);
            $property->addPropertyMapping($propertyMapping);
        }

        if (false === $importProperty->isAllowMultipleProperties() &&
            $propertyMapping->getExternalPropertyId() !== $importProperty->getExternalPropertyId()
        ) {
            $this->logger->warning(
                $message = sprintf(
                    'External property ids do not match for ImportProperty#%d (%s !== %s).',
                    $importProperty->getId(),
                    $propertyMapping->getExternalPropertyId(),
                    $importProperty->getExternalPropertyId()
                ),
                [
                    'group' => $importProperty->getImport()->getGroup(),
                    'additional_parameter' => $importProperty->getExternalPropertyId()
                ]
            );

            throw new ImportInvalidArgumentException($message);
        }

        if ($importProperty->isAddressHasUnits()) {
            $this->propertyManager->setupMultiUnitProperty($property);
        } else {
            try {
                $this->propertyManager->setupSingleProperty($property, ['doFlush' => false]);
            } catch (\RuntimeException $e) {
                $message = $this->logger->warning(
                    $e->getMessage(),
                    [
                        'group' => $importProperty->getImport()->getGroup(),
                        'additional_parameter' => $importProperty->getExternalPropertyId()
                    ]
                );
                throw new ImportLogicException($message);
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
     */
    protected function processUnit(Property $property, ImportProperty $importProperty)
    {
        try {
            $group = $importProperty->getImport()->getGroup();
            $unitMapping = $this->em
                ->getRepository('RjDataBundle:UnitMapping')
                ->getMappingForImport($group, $importProperty->getExternalUnitId());

            if ($unitMapping &&
                (!$property->getId() || $unitMapping->getUnit()->getProperty()->getId() != $property->getId())
            ) {
                $this->logger->warning(
                    $message = sprintf(
                        'Unit#%d found by external unit id and group but do not belong to processing Property#%d',
                        $unitMapping->getUnit()->getId(),
                        $property->getId()
                    ),
                    [
                        'group' => $importProperty->getImport()->getGroup(),
                        'additional_parameter' => $importProperty->getExternalPropertyId()
                    ]
                );
                throw new ImportLogicException($message);
            }

            if ($unitMapping) {
                $unit = $unitMapping->getUnit();
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
                            'additional_parameter' => $importProperty->getExternalPropertyId()
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

            // @cary: Our new import 2.0 assumptions are that "The Accounting System (or CSV) is the Source of Truth".
            //  So if the unit name is different from the A.S., then we should update it in our DB.
            $unit->setName($importProperty->getUnitName());

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
     * @param ImportProperty $importProperty
     *
     * @return bool true if we need create new Property
     */
    protected function isDifferentPropertyShouldBeCreated(
        Property $property,
        Group $group,
        ImportProperty $importProperty
    ) {
        if ($property->getId() === null) {
            return false;
        }

        // found property  but belongs to a different group -- so we need a different property record.
        if (!$property->getPropertyGroups()->isEmpty() && !$property->getPropertyGroups()->contains($group)) {
            return true;
        }

        // typically a given address maps to only one external_property_id within a holding.
        // However, we will create a different property and mapping at the same address, if explicitly allowed.
        $propertyMapping = $property->getPropertyMappingByHolding($group->getHolding());
        if (null !== $propertyMapping && true === $importProperty->isAllowMultipleProperties() &&
            $propertyMapping->getExternalPropertyId() !== $importProperty->getExternalPropertyId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param ImportProperty $importProperty
     *
     * @return Property|null
     */
    protected function getPropertyByImportProperty(ImportProperty $importProperty)
    {
        $this->logger->debug(
            sprintf('Try to find Property by Group and extPropertyId.', $importProperty->getId()),
            [
                'group' => $importProperty->getImport()->getGroup(),
                'additional_parameter' => $importProperty->getExternalPropertyId()
            ]
        );
        $properties = $this->getPropertyRepository()->findAllByGroupAndExternalId(
            $importProperty->getImport()->getGroup(),
            $importProperty->getExternalPropertyId()
        );

        if (false === empty($properties)) {
            if (null !== $property = $this->matchPropertyByInvalidIndex($importProperty, $properties)) {
                return $property;
            }
            if (null !== $property = $this->matchPropertyByAddress($importProperty, $properties)) {
                return $property;
            }
        }

        return $this->propertyManager->getOrCreatePropertyByAddress(
            $importProperty->getAddress1(),
            null,
            $importProperty->getCity(),
            $importProperty->getState(),
            $importProperty->getZip()
        );
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
     * @param ImportProperty $importProperty
     * @param array          $properties
     *
     * @throws ImportLogicException
     *
     * @return null|Property
     */
    protected function matchPropertyByInvalidIndex(ImportProperty $importProperty, array $properties)
    {
        $invalidIndex = PropertyManager::generateInvalidAddressIndex(
            $importProperty->getAddress1(),
            '',
            $importProperty->getCity(),
            $importProperty->getState()
        );

        $this->logger->debug(
            sprintf('Try to match Property by invalid index %s', $invalidIndex),
            [
                'group' => $importProperty->getImport()->getGroup(),
                'additional_parameter' => $importProperty->getExternalPropertyId()
            ]
        );

        $matchedProperty = null;
        /** @var Property $property */
        foreach ($properties as $property) {
            if ($property->getPropertyAddress()->getIndex() === $invalidIndex) {
                if ($matchedProperty !== null) {
                    throw new ImportLogicException('Duplicate addresses detected for external property id.');
                }
                $matchedProperty = $property;
            }
        }

        return $matchedProperty;
    }

    /**
     * @param ImportProperty $importProperty
     * @param array          $properties
     *
     * @throws ImportLogicException
     *
     * @return null|Property
     */
    protected function matchPropertyByAddress(ImportProperty $importProperty, array $properties)
    {
        $address = $this->propertyManager->lookupAddress(
            $importProperty->getAddress1(),
            $importProperty->getCity(),
            $importProperty->getState(),
            $importProperty->getZip()
        );

        if ($address === null) {
            return null;
        }

        $ssIndex = $address->getIndex();

        $this->logger->debug(
            sprintf('Try to match Property by invalid ssIndex %s', $ssIndex),
            [
                'group' => $importProperty->getImport()->getGroup(),
                'additional_parameter' => $importProperty->getExternalPropertyId()
            ]
        );

        $matchedProperty = null;
        /** @var Property $property */
        foreach ($properties as $property) {
            if ($property->getPropertyAddress()->getIndex() === $ssIndex) {
                if ($matchedProperty !== null) {
                    throw new ImportLogicException('Duplicate addresses detected for external property id.');
                }
                $matchedProperty = $property;
            }
        }

        return $matchedProperty;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PropertyRepository
     */
    protected function getPropertyRepository()
    {
        return $this->em->getRepository('RjDataBundle:Property');
    }
}
