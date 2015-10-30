<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Holding;
use Psr\Log\LogLevel;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use RentJeeves\ExternalApiBundle\Model\AMSI\RecurringCharge;
use RentJeeves\ExternalApiBundle\Services\AbstractContractSynchronizer;

/**
 * DI\Service("amsi.contract_sync")
 */
class ContractSynchronizer extends AbstractContractSynchronizer
{
    const LOGGER_PREFIX = '[AMSI ContractSynchronizer]';

    /**
     * {@inheritdoc}
     */
    protected function setExternalSettings(Holding $holding)
    {
        $this->residentDataManager->setSettings($holding->getAmsiSettings());
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingBalance()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingBalanceAMSI();
    }

    /**
     * @param Lease $lease
     * @param PropertyMapping $propertyMapping
     * @throws \Exception
     */
    protected function updateContractBalanceForResidentTransaction(
        $lease,
        PropertyMapping $propertyMapping
    ) {
        foreach ($lease->getOccupants() as $occupant) {
            try {
                if (null !== $contract = $this->getContract($propertyMapping, $lease, $occupant)) {
                    $this->logMessage(
                        sprintf(
                            '[SyncBalance]Processing %s #%d.',
                            (new \ReflectionObject($contract))->getShortName(),
                            $contract->getId()
                        )
                    );
                    $this->doUpdateBalanceAndPaymentAccepted($lease, $contract);
                }
            } catch (\Exception $e) {
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]ERROR: %s on %s:%d',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ),
                    LogLevel::ALERT
                );
            }
        }
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param Lease $lease
     * @param Occupant $occupant
     * @return null|Contract|ContractWaiting
     * @throws \Exception
     */
    protected function getContract(PropertyMapping $propertyMapping, Lease $lease, Occupant $occupant)
    {
        $residentId = $occupant->getOccuSeqNo();

        $this->logMessage(
            sprintf(
                'Getting contract for holding %s, propertyMapping %s, lease %s, residentId %s',
                $propertyMapping->getHolding()->getId(),
                $propertyMapping->getExternalPropertyId(),
                $lease->getExternalUnitId(),
                $residentId
            )
        );

        $contracts = $this
            ->getContractRepository()
            ->findContractsByPropertyMappingResidentAndExternalUnitId(
                $propertyMapping,
                $residentId,
                $lease->getExternalUnitId()
            );

        if (count($contracts) > 1) {
            throw new \LogicException(
                sprintf(
                    'Found more than one contract with property %s, externalUnitId %s, residentId %s',
                    $propertyMapping->getExternalPropertyId(),
                    $lease->getExternalUnitId(),
                    $residentId
                )
            );
        }

        if (count($contracts) == 1) {
            return reset($contracts);
        }

        $contractWaiting = null;
        try {
            $contractWaiting = $this
                ->getContractWaitingRepository()
                ->findOneByPropertyMappingExternalUnitIdAndResidentId(
                    $propertyMapping,
                    $lease->getExternalUnitId(),
                    $residentId
                );
        } catch (\Doctrine\ORM\NonUniqueResultException $e) {
            throw new \LogicException(
                sprintf(
                    'Duplicate mapping found cannot update balance: property %s, externalUnitId %s, resident %s',
                    $propertyMapping->getExternalPropertyId(),
                    $lease->getExternalUnitId(),
                    $residentId
                )
            );
        }
        if ($contractWaiting) {
            return $contractWaiting;
        }

        $this->logMessage(
            sprintf(
                'Could not find any contract with external property %s, externalUnitId %s, resident %s',
                $propertyMapping->getExternalPropertyId(),
                $lease->getExternalUnitId(),
                $residentId
            )
        );

        return null;
    }

    /**
     * @param Lease $lease
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdateBalanceAndPaymentAccepted(Lease $lease, $contract)
    {
        $disallow = $lease->getBlockPaymentAccess();
        $externalLeaseId = $lease->getResiId();
        $balance = $lease->getEndBalance();
        if (strtolower($disallow) === 'y') {
            $disallow = PaymentAccepted::DO_NOT_ACCEPT;
        } else {
            $disallow = PaymentAccepted::ANY;
        }
        $contract->setPaymentAccepted($disallow);
        $currentExternalLeaseId = $contract->getExternalLeaseId();
        if (empty($currentExternalLeaseId)) {
            $contract->setExternalLeaseId($externalLeaseId);
        }
        $contract->setIntegratedBalance($balance);
        $this->logMessage(
            sprintf(
                'Set value to update: payment accepted to %s, external lease to %s, balance to %s. For %s #%d',
                $disallow,
                $externalLeaseId,
                $balance,
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingRent()
    {
        return $this->getHoldingRepository()->findHoldingsForAMSISyncRecurringCharges();
    }

    /**
     * @param Lease $lease
     * @param PropertyMapping $propertyMapping
     */
    protected function updateContractRentForResidentTransaction($lease, PropertyMapping $propertyMapping)
    {
        $recurringCodes = $propertyMapping->getHolding()->getRecurringCodesArray();
        $sumRecurringCharges = $this->getSumRecurringCharges($lease, $recurringCodes);

        if ($sumRecurringCharges <= 0) {
            throw new \LogicException(
                sprintf(
                    'Sum of RecurringCharges for Holding#%d, PropertyMapping#%d, lease#%s = %d',
                    $propertyMapping->getHolding()->getId(),
                    $propertyMapping->getExternalPropertyId(),
                    $lease->getExternalUnitId(),
                    $sumRecurringCharges
                )
            );
        }

        foreach ($lease->getOccupants() as $occupant) {
            try {
                if (null !== $contract = $this->getContract($propertyMapping, $lease, $occupant)) {
                    $this->logMessage(
                        sprintf(
                            '[SyncRent]Processing %s #%d.',
                            (new \ReflectionObject($contract))->getShortName(),
                            $contract->getId()
                        )
                    );
                    $contract->setRent($sumRecurringCharges);
                    $this->logMessage(
                        sprintf(
                            '[SyncRent]Rent for %s #%d updated to %s',
                            (new \ReflectionObject($contract))->getShortName(),
                            $contract->getId(),
                            $sumRecurringCharges
                        )
                    );
                }
            } catch (\Exception $e) {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]ERROR: %s on %s:%d',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ),
                    LogLevel::ALERT
                );
            }
        }
    }

    /**
     * @param Lease $lease
     * @param array $recurringCodes
     *
     * @return int
     */
    protected function getSumRecurringCharges(Lease $lease, array $recurringCodes)
    {
        $sumRecurringCharges = 0;
        /** @var RecurringCharge $recurringCharge */
        foreach ($lease->getRecurringCharges() as $recurringCharge) {
            if ((empty($recurringCodes) || in_array($recurringCharge->getIncCode(), $recurringCodes)) &&
                $recurringCharge->getFreqCode() === 'M'
            ) {
                $sumRecurringCharges += $recurringCharge->getAmount();
            }
        }

        return $sumRecurringCharges;
    }
}
