<?php

namespace RentJeeves\ImportBundle\PropertyImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Entity\Property;
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
 * Service`s name "import.property.loader.unmapped"
 */
class UnmappedLoader implements PropertyLoaderInterface
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
     * {@inheritdoc}
     */
    public function loadData(Import $import, $additionalParameter = null)
    {
        $this->logger->info(
            sprintf(
                'Starting process unmapped load property from Import#%d.',
                $import->getId()
            ),
            ['group' => $import->getGroup(), 'additional_parameter' => $additionalParameter]
        );

        $iterableResult = $this->em
            ->getRepository('RjDataBundle:ImportProperty')
            ->getNotProcessedImportProperties($import);
        /** @var ImportProperty $importProperty */
        while ((list($importProperty) = $iterableResult->next()) !== false) {
            $this->processImportProperty($importProperty);
            $this->em->flush($importProperty);
            $this->em->clear();
        }

        $this->logger->info(
            sprintf(
                'Finished process unmapped load property from Import#%d.',
                $import->getId()
            ),
            ['group' => $import->getGroup(), 'additional_parameter' => $additionalParameter]
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
                'group' => $importProperty->getImport()->getGroup()
            ]
        );

        try {
            $property = $this->processProperty($importProperty);

            if (!$property->isSingle()) {
                $unit = $this->processUnit($property, $importProperty);
            }
            $this->em->flush([
                $property,
                $property->getPropertyMappingByHolding($importProperty->getImport()->getGroup()->getHolding())
            ]);

            if (!$property->getId()) {
                $importProperty->setStatus(ImportPropertyStatus::NEW_PROPERTY_AND_UNIT);
            } elseif (isset($unit) && !$unit->getId()) {
                $importProperty->setStatus(ImportPropertyStatus::NEW_UNIT);
            } else {
                $importProperty->setStatus(ImportPropertyStatus::MATCH);
            }
        } catch (ImportException $e) {
            $this->logger->error(
                sprintf('%s on %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()),
                [
                    'group' => $importProperty->getImport()->getGroup(),
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

        $address = $this->mapAddress($importProperty);

        $property = $this->propertyManager->getOrCreatePropertyByAddress($address);

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

        if (true === $this->isDifferentPropertyShouldBeCreated($property, $group, $importProperty)) {
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
                $message = $this->logger->warning(
                    $e->getMessage(),
                    [
                        'group' => $importProperty->getImport()->getGroup()
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
     * @param ImportProperty $importProperty
     * @return Address
     */
    protected function mapAddress(ImportProperty $importProperty)
    {
        $address = new Address();
        $address->setStreet($importProperty->getAddress1());
        $address->setUnitName($importProperty->getUnitName());
        $address->setCity($importProperty->getCity());
        $address->setState($importProperty->getState());
        $address->setZip($importProperty->getZip());

        return $address;
    }
}
