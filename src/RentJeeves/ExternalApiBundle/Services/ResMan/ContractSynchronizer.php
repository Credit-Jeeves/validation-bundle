<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Model\ResMan\Detail;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtServiceTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transactions;
use RentJeeves\ExternalApiBundle\Services\AbstractContractSynchronizer;

/**
 * DI\Service("resman.contract_sync")
 */
class ContractSynchronizer extends AbstractContractSynchronizer
{
    const LOGGER_PREFIX = '[ResMan ContractSynchronizer]';

    /**
     * {@inheritdoc}
     */
    protected function setExternalSettings(Holding $holding)
    {
        $this->residentDataManager->setSettings($holding->getResManSettings());
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingBalance()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingBalanceResMan();
    }

    /**
     * @param RtCustomer $resident
     * @param PropertyMapping $propertyMapping
     */
    protected function updateContractBalanceForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    ) {
        if ($resident->getCustomers()->getCustomer()->isEmpty()) {
            $this->logMessage(
                sprintf(
                    '[SyncBalance]Skip RtCustomer(%s) with empty customer collection for external property "%s".',
                    $resident->getCustomerId(),
                    $propertyMapping->getExternalPropertyId()
                )
            );

            return;
        }
        if (null === $contracts = $this->getContractsForUpdateBalance($resident, $propertyMapping)) {
            return;
        }

        foreach ($contracts as $contract) {
            $this->logMessage(
                sprintf(
                    '[SyncBalance]Processing %s #%d.',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId()
                )
            );
            $this->doUpdateBalanceAndPaymentAccepted($resident, $contract);
        }
    }

    /**
     * @param RtCustomer $customerBase
     * @param PropertyMapping $propertyMapping
     * @return null|Contract[]|ContractWaiting[]
     */
    protected function getContractsForUpdateBalance(RtCustomer $customerBase, PropertyMapping $propertyMapping)
    {
        $externalLeaseId = $customerBase->getCustomerId();
        $unitName = $customerBase->getRtUnit()->getUnitId();
        $contracts = $this
            ->getContractRepository()
            ->findContractByHoldingPropertyExternalLeaseIdUnitAndIntegratedGroup(
                $propertyMapping->getHolding(),
                $propertyMapping->getProperty(),
                $externalLeaseId,
                $unitName
            );

        if (!empty($contracts)) {
            $this->logMessage(
                sprintf(
                    '[SyncBalance]Found %d contracts with propertyId %s, unitName %s, $externalLeaseId %s',
                    count($contracts),
                    $propertyMapping->getProperty()->getId(),
                    $unitName,
                    $externalLeaseId
                )
            );

            return $contracts;
        }

        $contractsWaiting = $this
            ->getContractWaitingRepository()
            ->findByHoldingPropertyUnitExternalLeaseId(
                $propertyMapping->getHolding(),
                $propertyMapping->getProperty(),
                $unitName,
                $externalLeaseId
            );

        if (!empty($contractsWaiting)) {
            $this->logMessage(
                sprintf(
                    '[SyncBalance]Found %s contractsWaiting with propertyId %s, unitName %s, $externalLeaseId %s',
                    count($contractsWaiting),
                    $propertyMapping->getProperty()->getId(),
                    $unitName,
                    $externalLeaseId
                )
            );

            return $contractsWaiting;
        }

        $this->logMessage(
            sprintf(
                '[SyncBalance]Could not find any contract with external property %s, unitName %s, externalLeaseId %s',
                $propertyMapping->getExternalPropertyId(),
                $unitName,
                $externalLeaseId
            )
        );

        return null;
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdateBalanceAndPaymentAccepted(RtCustomer $baseCustomer, $contract)
    {
        $contract->setPaymentAccepted($baseCustomer->getRentTrackPaymentAccepted());
        $contract->setIntegratedBalance($baseCustomer->getRentTrackBalance());
        $this->logMessage(
            sprintf(
                '[SyncBalance]Payment accepted set to %s and balance to %s. For %s #%d',
                $contract->getPaymentAccepted(),
                $contract->getIntegratedBalance(),
                $contract->getId(),
                (new \ReflectionObject($contract))->getShortName()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getHoldingsForUpdatingRent()
    {
        return $this->getHoldingRepository()->findHoldingsForResmanSyncRecurringCharges();
    }

    /**
     * @param RtServiceTransactions $resident
     * @param PropertyMapping $propertyMapping
     *
     */
    protected function updateContractRentForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    ) {
        if (null === $contracts = $this->getContractsForUpdateRent($resident, $propertyMapping)) {
            return;
        }

        foreach ($contracts as $contract) {
            $this->logMessage(
                sprintf(
                    '[SyncRent]Processing %s #%d.',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId()
                )
            );
            $recurringCodes = $propertyMapping->getHolding()->getRecurringCodesArray();
            $sumRecurringCharges = $this->getSumRecurringCharges($resident, $recurringCodes);

            if ($sumRecurringCharges <= 0) {
                throw new \LogicException(
                    sprintf(
                        'Sum of RecurringCharges for %s #%d = %d',
                        (new \ReflectionObject($contract))->getShortName(),
                        $contract->getId(),
                        $sumRecurringCharges
                    )
                );
            }
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
    }

    /**
     * @param RtServiceTransactions $rtServiceTransaction
     * @param array $recurringCodes
     *
     * @return float
     */
    protected function getSumRecurringCharges(RtServiceTransactions $rtServiceTransaction, array $recurringCodes)
    {
        $sumRecurringCharges = 0;
        /** @var Transactions $transaction */
        foreach ($rtServiceTransaction->getTransactions() as $transaction) {
            if ($transaction->getCharge() !== null) {
                $details = $transaction->getCharge()->getDetail();
                if (empty($recurringCodes) || true === in_array($details->getChargeCode(), $recurringCodes)) {
                    // Need to strip out commas
                    $chargeAmount = str_replace(',', '', $details->getAmount());
                    $sumRecurringCharges += $chargeAmount;
                }
            } elseif ($transaction->getConcession() !== null) {
                $details = $transaction->getConcession()->getDetail();
                if (empty($recurringCodes) || true === in_array($details->getChargeCode(), $recurringCodes)) {
                    $concessionAmount = str_replace(',', '', $details->getAmount());
                    $sumRecurringCharges -= $concessionAmount;
                }
            }
        }

        return $sumRecurringCharges;
    }

    /**
     * @param PropertyMapping $propertyMapping
     *
     * @return null|Contract[]|ContractWaiting[]
     */
    protected function getContractsForUpdateRent(
        RtServiceTransactions $rtServiceTransaction,
        PropertyMapping $propertyMapping
    ) {
        if (null === $firstDetail = $this->getFirstDetailForRTServiceTransaction($rtServiceTransaction)) {
            throw new \InvalidArgumentException(
                'rtServiceTransaction does not have details.'
            );
        }
        $contracts = $this
            ->getContractRepository()
            ->findContractByHoldingPropertyExternalLeaseIdUnitAndIntegratedGroup(
                $propertyMapping->getHolding(),
                $propertyMapping->getProperty(),
                $firstDetail->getCustomerId(),
                $firstDetail->getUnitID()
            );

        if (!empty($contracts)) {
            $this->logMessage(
                sprintf(
                    '[SyncRent]Found %d contracts with propertyId %s, externalLeaseId %s and unitName "%s"',
                    count($contracts),
                    $propertyMapping->getProperty()->getId(),
                    $firstDetail->getCustomerId(),
                    $firstDetail->getUnitID()
                )
            );

            return $contracts;
        }

        $contractsWaiting = $this
            ->getContractWaitingRepository()
            ->findByHoldingPropertyUnitExternalLeaseId(
                $propertyMapping->getHolding(),
                $propertyMapping->getProperty(),
                $firstDetail->getUnitID(),
                $firstDetail->getCustomerId()
            );

        if (!empty($contractsWaiting)) {
            $this->logMessage(
                sprintf(
                    '[SyncRent]Found %d contractsWaiting with propertyId %s, externalLeaseId %s and unitName "%s"',
                    count($contractsWaiting),
                    $propertyMapping->getProperty()->getId(),
                    $firstDetail->getCustomerId(),
                    $firstDetail->getUnitID()
                )
            );

            return $contractsWaiting;
        }

        $this->logMessage(
            sprintf(
                '[SyncBalance]Could not find any contract with external property %s, unitName %s, externalLeaseId %s',
                $propertyMapping->getExternalPropertyId(),
                $firstDetail->getUnitID(),
                $firstDetail->getCustomerId()
            )
        );

        return null;
    }

    /**
     * @param RtServiceTransactions $rtServiceTransaction
     *
     * @return null|Detail
     */
    protected function getFirstDetailForRTServiceTransaction(RtServiceTransactions $rtServiceTransaction)
    {
        /** @var Transactions $firstTransaction */
        $firstTransaction = $rtServiceTransaction->getTransactions()[0];
        if (null !== $firstTransaction->getCharge()) {
            return $firstTransaction->getCharge()->getDetail();
        } elseif (null !== $firstTransaction->getConcession()) {
            return $firstTransaction->getConcession()->getDetail();
        }

        return null;
    }

    /**
     * @param RtServiceTransactions $rtServiceTransaction
     * @param array $recurringCodes
     *
     * @return float
     */
    protected function getSumRecurringCharges(RtServiceTransactions $rtServiceTransaction, array $recurringCodes)
    {
        $sumRecurringCharges = 0;
        /** @var Transactions $transaction */
        foreach ($rtServiceTransaction->getTransactions() as $transaction) {
            if ($transaction->getCharge() !== null) {
                $details = $transaction->getCharge()->getDetail();
                if (empty($recurringCodes) || true === in_array($details->getChargeCode(), $recurringCodes)) {
                    $sumRecurringCharges += $details->getAmount();
                }
            } elseif ($transaction->getConcession() !== null) {
                $details = $transaction->getConcession()->getDetail();
                if (empty($recurringCodes) || true === in_array($details->getChargeCode(), $recurringCodes)) {
                    $sumRecurringCharges -= $details->getAmount();
                }
            }
        }

        return $sumRecurringCharges;
    }
}
