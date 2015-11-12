<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
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
     * @param Holding $holding
     * @param RtCustomer $resident
     * @param string $externalPropertyId
     * @return Contract[]|ContractWaiting[]
     */
    protected function getContractsForUpdatingBalance(
        Holding $holding,
        $resident,
        $externalPropertyId
    ) {
        $roommates = $resident->getCustomers()->getCustomer();
        if ($roommates->isEmpty()) {
            $this->logMessage(
                sprintf(
                    '[SyncBalance]Skip RtCustomer(%s) with empty customer collection for external property "%s".',
                    $resident->getCustomerId(),
                    $externalPropertyId
                )
            );

            return [];
        }

        $allContracts = [];
        /** @var Customer $roommate */
        foreach ($roommates as $roommate) {
            $residentId = $roommate->getCustomerId();
            $externalUnitId = $roommate->getExternalUnitId($resident);
            $contracts = $this
                ->getContractRepository()
                ->findContractsByHoldingExternalPropertyResidentExternalUnitId(
                    $holding,
                    $externalPropertyId,
                    $residentId,
                    $externalUnitId
                );
            empty($contracts) || $allContracts = array_merge($allContracts, $contracts);

            $contractsWaiting = $this
                ->getContractWaitingRepository()
                ->findContractsByHoldingExternalPropertyResidentExternalUnitId(
                    $holding,
                    $externalPropertyId,
                    $residentId,
                    $externalUnitId
                );

            empty($contractsWaiting) || $allContracts = array_merge($allContracts, $contractsWaiting);
        }

        $count = count($allContracts);
        $this->logMessage(
            sprintf(
                '[SyncBalance]%s contracts for processing' .
                ' by external property "%s" of holding "%s" #%d and leaseId (main resident Id) "%s"',
                $count ? 'Found ' . $count : 'Not found any',
                $externalPropertyId,
                $holding->getName(),
                $holding->getId(),
                $resident->getCustomerId()
            )
        );

        return $allContracts;
    }

    /**
     * @param Contract|ContractWaiting $contract
     * @param RtCustomer $baseCustomer
     *
     */
    protected function updateContractBalanceForResidentTransaction($contract, $baseCustomer)
    {
        $contract->setPaymentAccepted($baseCustomer->getRentTrackPaymentAccepted());
        $this->logMessage(
            sprintf(
                '[SyncBalance]Setup payment accepted to %s, for residentId %s',
                $contract->getPaymentAccepted(),
                $baseCustomer->getCustomerId()
            )
        );
        $externalLeaseId = $contract->getExternalLeaseId();
        if (empty($externalLeaseId)) {
            $contract->setExternalLeaseId($baseCustomer->getCustomerId());
            $this->logMessage(
                sprintf(
                    '[SyncBalance]%s #%d externalLeaseId has been updated. ExternalLeaseId set to #%s',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId(),
                    $baseCustomer->getCustomerId()
                )
            );
        }
        $contract->setIntegratedBalance($baseCustomer->getRentTrackBalance());
        $this->logMessage(
            sprintf(
                '[SyncBalance]%s #%s has been updated. Now the balance is $%s',
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId(),
                $baseCustomer->getRentTrackBalance()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingRent()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingRentResMan();
    }

    /**
     * @param Holding $holding
     * @param RtServiceTransactions $resident
     * @param string $externalPropertyId
     */
    protected function processingResidentForUpdateRent(
        Holding $holding,
        $resident,
        $externalPropertyId
    ) {
        $recurringCodes = $holding->getRecurringCodesArray();
        $sumRecurringCharges = $this->getSumRecurringCharges($resident, $recurringCodes);
        $firstDetails = $this->getFirstDetailForRTServiceTransaction($resident);
        if (!$firstDetails) {
            throw new \LogicException(
                sprintf(
                    '[SyncRent]Can not get',
                    $holding->getName(),
                    $holding->getId(),
                    $firstDetails ? $firstDetails->getCustomerId() : '',
                    $sumRecurringCharges
                )
            );
        }
        if ($sumRecurringCharges <= 0) {
            throw new \LogicException(
                sprintf(
                    '[SyncRent]Sum of RecurringCharges for %s #%d  and lease Id "%s" = %d',
                    $holding->getName(),
                    $holding->getId(),
                    $firstDetails ? $firstDetails->getCustomerId() : '',
                    $sumRecurringCharges
                )
            );
        }

        $leaseId = $firstDetails->getCustomerId();
        $unitName = $firstDetails->getUnitID();

        $allContracts = [];

        $contracts = $this
            ->getContractRepository()
            ->findContractsByHoldingExternalPropertyLeaseUnit($holding, $externalPropertyId, $leaseId, $unitName);

        empty($contracts) || $allContracts = array_merge($allContracts, $contracts);

        $contractsWaiting = $this
            ->getContractWaitingRepository()
            ->findContractsByHoldingExternalPropertyLeaseUnit($holding, $externalPropertyId, $leaseId, $unitName);

        empty($contractsWaiting) || $allContracts = array_merge($allContracts, $contractsWaiting);

        /** @var Contract|ContractWaiting $contract */
        foreach ($allContracts as $contract) {
            $contract->setRent($sumRecurringCharges);
            $this->logMessage(
                sprintf(
                    '[SyncRent]Rent for %s #%d updated to %s',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId(),
                    $contract->getRent()
                )
            );
            $this->em->flush();
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
     * @param RtServiceTransactions $rtServiceTransaction
     *
     * @return null|Detail
     */
    protected function getFirstDetailForRTServiceTransaction(RtServiceTransactions $rtServiceTransaction)
    {
        if (count($rtServiceTransaction->getTransactions()) > 0) {
            /** @var Transactions $firstTransaction */
            $firstTransaction = $rtServiceTransaction->getTransactions()[0];
            if (null !== $firstTransaction->getCharge()) {
                return $firstTransaction->getCharge()->getDetail();
            } elseif (null !== $firstTransaction->getConcession()) {
                return $firstTransaction->getConcession()->getDetail();
            }
        }

        return null;
    }
}
