<?php

namespace RentJeeves\CoreBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Exception\PropertyDeduplicatorException;
use RentJeeves\CoreBundle\Exception\UnitDeduplicatorException;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\PropertyMapping;

class PropertyDeduplicator
{
    /**
     * @var UnitDeduplicator
     */
    protected $unitDeduplicator;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $dryRunMode = false;

    /**
     * @param UnitDeduplicator $unitDeduplicator
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(
        UnitDeduplicator $unitDeduplicator,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->unitDeduplicator = $unitDeduplicator;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param boolean $dryRunMode
     */
    public function setDryRunMode($dryRunMode)
    {
        $this->dryRunMode = (boolean) $dryRunMode;
    }

    /**
     * @param PropertyAddress $propertyAddress
     *
     * @throws PropertyDeduplicatorException
     */
    public function deduplicate(PropertyAddress $propertyAddress)
    {
        if (null === $destinationProperty = $this->findFirstPropertyByPropertyAddress($propertyAddress)) {
            $this->logger->warning(
                $message = sprintf(
                    'Not found any Properties for PropertyAddress#%d',
                    $propertyAddress->getId()
                )
            );

            throw new PropertyDeduplicatorException($message);
        }

        $sourceProperties = $this->findAllOtherPropertiesWithSamePropertyAddress($destinationProperty);
        if (true === empty($sourceProperties)) {
            $this->logger->warning(
                $message = sprintf(
                    'Not found any dubbed Properties for Property#%d and PropertyAddress#%d',
                    $destinationProperty->getId(),
                    $propertyAddress->getId()
                )
            );

            throw new PropertyDeduplicatorException($message);
        }

        $this->checkExternalMappingForProperties($destinationProperty, $sourceProperties);
        $this->checkGroupMappingForProperties($destinationProperty, $sourceProperties);
        $this->deduplicateProperties($destinationProperty, $sourceProperties);
    }

    /**
     * @param Property $dstProperty
     * @param array|Property[] $srcProperties
     *
     * @throws PropertyDeduplicatorException there are problems with PropertyMappings
     */
    protected function checkExternalMappingForProperties(Property $dstProperty, array $srcProperties)
    {
        $dstExternalPropertyId = null;
        if ($dstProperty->getPropertyMappings()->count() > 1) {
            $this->logger->warning(
                $message = sprintf(
                    'ERROR: Property#%d is used by several different holdings cannot deduplicate multi-holding' .
                    'properties, please resolve conflict manually then rerun',
                    $dstProperty->getId()
                )
            );

            throw new PropertyDeduplicatorException($message);
        } elseif ($dstProperty->getPropertyMappings()->count() === 1) {
            $dstExternalPropertyId = $dstProperty->getPropertyMappings()->first()->getExternalPropertyId();
        }

        foreach ($srcProperties as $srcProperty) {
            if ($srcProperty->getPropertyMappings()->count() > 1) {
                $this->logger->warning(
                    $message = sprintf(
                        'ERROR: Property#%d is used by several different holdings cannot deduplicate multi-holding ' .
                        'properties, please resolve conflict manually then rerun',
                        $srcProperty->getId()
                    )
                );

                throw new PropertyDeduplicatorException($message);
            } elseif ($srcProperty->getPropertyMappings()->count() === 1 && $dstExternalPropertyId !== null) {
                if ($dstExternalPropertyId !== $srcProperty->getPropertyMappings()->first()->getExternalPropertyId()) {
                    $this->logger->warning(
                        $message = sprintf(
                            'ERROR: the externalPropertyId="%s" of the dstProperty#%d is different ' .
                            'than the srcProperty#%d, please resolve conflict manually',
                            $dstExternalPropertyId,
                            $dstProperty->getId(),
                            $srcProperty->getId()
                        )
                    );

                    throw new PropertyDeduplicatorException($message);
                }
            }
        }
    }

    /**
     * @param Property $dstProperty
     * @param array|Property[] $srcProperties
     *
     * @throws PropertyDeduplicatorException dstProperty and srcProperty have different sets of Group
     */
    protected function checkGroupMappingForProperties(Property $dstProperty, array $srcProperties)
    {
        $dstGroups = $dstProperty->getPropertyGroups()->getValues();
        foreach ($srcProperties as $srcProperty) {
            $srcGroups = $srcProperty->getPropertyGroups()->getValues();
            if (false === $this->checkIdentity2GroupSets($dstGroups, $srcGroups)) {
                $this->logger->warning(
                    $message = sprintf(
                        'ERROR: dstProperty#%d and srcProperty#%d have different sets of Group, ' .
                        'please resolve conflict manually.',
                        $dstProperty->getId(),
                        $srcProperty->getId()
                    )
                );

                throw new PropertyDeduplicatorException($message);
            }
        }
    }

    /**
     * @param Property $destinationProperty
     * @param Property[] $sourceProperties
     */
    protected function deduplicateProperties(Property $destinationProperty, array $sourceProperties)
    {
        // Get id`s - because we do `em->clear`
        $destinationPropertyId = $destinationProperty->getId();
        $sourcePropertyIds = [];
        foreach ($sourceProperties as $sourceProperty) {
            $sourcePropertyIds[] = $sourceProperty->getId();
        }

        $this->unitDeduplicator->setDryRunMode($this->dryRunMode);
        foreach ($sourcePropertyIds as $sourcePropertyId) {
            $sourceProperty = $this->findProperty($sourcePropertyId);
            $destinationProperty = $this->findProperty($destinationPropertyId);
            try {
                $this->deduplicateProperty($sourceProperty, $destinationProperty);
                $this->logger->info(
                    sprintf(
                        'Property#%d is deduplicated.',
                        $sourcePropertyId
                    )
                );
            } catch (UnitDeduplicatorException $e) {
                $this->logger->warning(
                    sprintf(
                        'ERROR: Can`t dedupe Property#%d : %s',
                        $sourcePropertyId,
                        $e->getMessage()
                    )
                );
            }
            // Clear all entities, which created in `unitDeduplicator`
            $this->em->clear();
        }
    }

    /**
     * @param Property $sourceProperty
     * @param Property $destinationProperty
     *
     * @throws UnitDeduplicatorException can`t dedupe units
     */
    protected function deduplicateProperty(Property $sourceProperty, Property $destinationProperty)
    {
        foreach ($sourceProperty->getUnits() as $srcUnit) {
            $this->unitDeduplicator->deduplicate($srcUnit, $destinationProperty);
        }
        if (true === $sourceProperty->isSingle() && false !== $sourceProperty->getUnits()->first()) {
            $this->disableSoftDelete();
            $this->em->remove($sourceProperty->getUnits()->first());
        }
        if ($sourceProperty->getPropertyMappings()->count() === 1) {
            /** @var PropertyMapping $sourcePropertyMapping */
            $sourcePropertyMapping = $sourceProperty->getPropertyMappings()->first();
            $sourcePropertyMapping->setProperty($destinationProperty);
        }

        $sourceProperty->setGroups(null); // remove all propertyGroups
        $this->em->remove($sourceProperty);
        if (false === $this->dryRunMode) {
            $this->em->flush();
        }
    }

    /**
     * @param Group[] $dstGroups
     * @param Group[] $srcGroups
     *
     * @return boolean true if arrays are Identity
     */
    protected function checkIdentity2GroupSets(array $dstGroups, array $srcGroups)
    {
        $dstGroupsIds = [];
        $srcGroupsIds = [];
        foreach ($dstGroups as $group) {
            $dstGroupsIds[] = $group->getId();
        }
        foreach ($srcGroups as $group) {
            $srcGroupsIds[] = $group->getId();
        }

        $differentGroups = array_merge(
            array_diff($dstGroupsIds, $srcGroupsIds),
            array_diff($srcGroupsIds, $dstGroupsIds)
        );

        return empty($differentGroups);
    }

    /**
     * TurnOn hard delete for Units
     */
    protected function disableSoftDelete()
    {
        if (null !== $eventManager = $this->em->getEventManager()) {
            foreach ($eventManager->getListeners() as $eventName => $listeners) {
                foreach ($listeners as $listener) {
                    if ($listener instanceof \Gedmo\SoftDeleteable\SoftDeleteableListener) {
                        $this->em->getEventManager()->removeEventListener($eventName, $listener);
                    }
                }
            }
        }
    }

    /**
     * @param PropertyAddress $propertyAddress
     *
     * @return null|\RentJeeves\DataBundle\Entity\Property
     */
    protected function findFirstPropertyByPropertyAddress(PropertyAddress $propertyAddress)
    {
        return $this->em->getRepository('RjDataBundle:Property')->findOneBy(
            ['propertyAddress' => $propertyAddress],
            ['id' => 'asc'] // "dstProperty ID — this is the dup with the loweset ID"
        );
    }

    /**
     * @param Property $property
     *
     * @return Property[]
     */
    protected function findAllOtherPropertiesWithSamePropertyAddress(Property $property)
    {
        return $this->em->getRepository('RjDataBundle:Property')
            ->findAllOtherPropertiesWithSamePropertyAddress($property);
    }

    /**
     * @param int $id
     *
     * @return Property
     */
    protected function findProperty($id)
    {
        return $this->em->getRepository('RjDataBundle:Property')->find($id);
    }
}
