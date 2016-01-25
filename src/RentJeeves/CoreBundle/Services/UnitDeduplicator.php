<?php

namespace RentJeeves\CoreBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Exception\ContractMovementManagerException;
use RentJeeves\CoreBundle\Exception\UnitDeduplicatorException;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;

/**
 * Service name "dedupe.unit_deduplicator"
 */
class UnitDeduplicator
{
    /**
     * @var ContractMovementManager
     */
    protected $contractMovement;

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
     * @param ContractMovementManager $contractMovement
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContractMovementManager $contractMovement,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->contractMovement = $contractMovement;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param Unit $sourceUnit
     * @param Property $destinationProperty
     *
     * @throws UnitDeduplicatorException if we can`t deduplicate Unit
     */
    public function deduplicate(Unit $sourceUnit, Property $destinationProperty)
    {
        $isSingleDestinationProperty = $destinationProperty->getPropertyAddress()->isSingle();
        $isSingleSourceProperty = $sourceUnit->getProperty()->getPropertyAddress()->isSingle();
        if ($isSingleDestinationProperty !== $isSingleSourceProperty) {
            $this->logger->warning(
                $message = sprintf(
                    'ERROR: can`t deduplicate Unit#%d from srcProperty#%d to dstProperty#%d, ' .
                    'because they have different isSingle values.',
                    $sourceUnit->getId(),
                    $sourceUnit->getProperty()->getId(),
                    $destinationProperty->getId()
                )
            );

            throw new UnitDeduplicatorException($message);
        }

        $propertyGroups = $destinationProperty->getPropertyGroups();
        if (false === $propertyGroups->contains($sourceUnit->getGroup())) {
            $this->logger->warning(
                $message = sprintf(
                    'ERROR: the dstProperty#%d is not in the same group as the srcUnit#%d, please resolve manually',
                    $destinationProperty->getId(),
                    $sourceUnit->getId()
                )
            );

            throw new UnitDeduplicatorException($message);
        }

        $destinationUnit = $this->findFirstUnitWithSameNameInDstProperty(
            $sourceUnit,
            $destinationProperty
        );
        if (null === $destinationUnit) {
            $destinationUnit = new Unit();
            $destinationUnit->setProperty($destinationProperty);
            $destinationUnit->setGroup($sourceUnit->getGroup());
            $destinationUnit->setHolding($sourceUnit->getHolding());
            $destinationUnit->setName($sourceUnit->getActualName());

            if (false === $this->dryRunMode) {
                $this->em->persist($destinationUnit);
            }
            $this->logger->info(
                sprintf(
                    'New Unit with name "%s" is created.',
                    $sourceUnit->getActualName()
                )
            );
        }

        $this->updateContractsWaitingForUnit($sourceUnit, $destinationUnit);
        $this->updateContractsForUnit($sourceUnit, $destinationUnit);
        $this->updateExternalUnitMapping($sourceUnit, $destinationUnit);

        if (false === $this->dryRunMode) {
            if (true !== $isSingleSourceProperty) { // not remove units from isSingle Property
                $this->disableSoftDelete();
                // need for remove old relations with contract (without it, contract is removed)
                $this->em->refresh($sourceUnit);
                $this->em->remove($sourceUnit);
            }
            $this->em->flush();
            $this->logger->info(
                true === $isSingleSourceProperty ? 'SrcUnit is deduplicated, but not deleted.' : 'SrcUnit is deleted.'
            );
        }

        $this->logger->info('Migration Units and Contracts from one Property to another is finished.');
    }

    /**
     * @param boolean $dryRunMode
     */
    public function setDryRunMode($dryRunMode)
    {
        $this->dryRunMode = (boolean) $dryRunMode;
    }

    /**
     * @param Unit $sourceUnit
     * @param Unit $destinationUnit
     *
     * @throws UnitDeduplicatorException if we can`t update UnitMapping
     */
    protected function updateExternalUnitMapping(Unit $sourceUnit, Unit $destinationUnit)
    {
        if (null === $sourceUnitMapping = $sourceUnit->getUnitMapping()) {
            return;
        }

        if (null !== $destinationUnitMapping = $destinationUnit->getUnitMapping()) {
            if ($sourceUnitMapping->getExternalUnitId() !== $destinationUnitMapping->getExternalUnitId()) {
                $this->logger->warning(
                    $message = sprintf(
                        'ERROR: the externalUnitID=%s of the dstUnit#%d is different than the externalUnitID=%s ' .
                        'of the srcUnitId#%d, please resolve conflict manually.',
                        $destinationUnitMapping->getExternalUnitId(),
                        $destinationUnit->getId(),
                        $sourceUnitMapping->getExternalUnitId(),
                        $sourceUnit->getId()
                    )
                );

                throw new UnitDeduplicatorException($message);
            }
        }

        if (true == $this->checkGroupHasOtherUnitsWithSameExternalUnitIdExcludeDstUnit($sourceUnit, $destinationUnit)) {
            $this->logger->warning(
                $message = sprintf(
                    'ERROR: there are multiple external unit ID="%s" in Group#%d, please resolve manually.',
                    $sourceUnitMapping->getExternalUnitId(),
                    $sourceUnit->getGroup()->getId()
                )
            );

            throw new UnitDeduplicatorException($message);
        }

        if (null === $destinationUnitMapping) {
            $sourceUnitMapping->setUnit($destinationUnit);
            $this->logger->info(
                sprintf(
                    'The UnitMapping#%d is now associated to the Unit#%d.',
                    $sourceUnitMapping->getId(),
                    $destinationUnit->getId()
                )
            );
        } elseif ($sourceUnitMapping->getExternalUnitId() === $destinationUnitMapping->getExternalUnitId()) {
            $this->em->remove($sourceUnitMapping);
            $this->logger->info(
                sprintf(
                    'The UnitMapping for Unit#%d is deleted.',
                    $sourceUnit->getId()
                )
            );
        }
    }

    /**
     * @param Unit $srcUnit
     * @param Unit $dstUnit
     */
    protected function updateContractsWaitingForUnit(Unit $srcUnit, Unit $dstUnit)
    {
        foreach ($srcUnit->getContractsWaiting() as $contractWaiting) {
            $contractWaiting->setProperty($dstUnit->getProperty());
            $contractWaiting->setUnit($dstUnit);

            $this->logger->info(
                sprintf(
                    'The contractWaiting#%d is updated.',
                    $contractWaiting->getId()
                )
            );
        }
    }

    /**
     * @param Unit $srcUnit
     * @param Unit $dstUnit
     *
     * @throws UnitDeduplicatorException if can`t update contracts
     */
    protected function updateContractsForUnit(Unit $srcUnit, Unit $dstUnit)
    {
        $this->contractMovement->setDryRunMode($this->dryRunMode);
        foreach ($srcUnit->getContracts() as $contract) {
            try {
                $this->contractMovement->move($contract, $dstUnit);
            } catch (ContractMovementManagerException $e) {
                $this->logger->warning(
                    $message = sprintf(
                        'Can`t update Unit#%d for Contract#%d : %s',
                        $dstUnit->getId(),
                        $contract->getId(),
                        $e->getMessage()
                    )
                );

                throw new UnitDeduplicatorException($message);
            }
        }
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
     * @param Unit $unit
     * @param Property $property
     *
     * @return Unit|null
     */
    protected function findFirstUnitWithSameNameInDstProperty(Unit $unit, Property $property)
    {
        return $this->em->getRepository('RjDataBundle:Unit')->findFirstUnitsWithSameNameByUnitAndPropertyAndSortById(
            $unit,
            $property
        );
    }

    /**
     * @param Unit $srcUnit
     * @param Unit $dstUnit
     *
     * @return boolean
     */
    protected function checkGroupHasOtherUnitsWithSameExternalUnitIdExcludeDstUnit(Unit $srcUnit, Unit $dstUnit)
    {
        $units = $this->em->getRepository('RjDataBundle:Unit')->findOtherUnitsWithSameExternalUnitIdInGroupExcludeUnit(
            $srcUnit,
            $dstUnit
        );

        return count($units) > 0;
    }
}
