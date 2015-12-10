<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
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
     * @param Holding $holding
     * @param Lease $lease
     * @param string $externalPropertyId
     * @return Contract[]|ContractWaiting[]
     */
    protected function getContractsForUpdatingBalance(
        Holding $holding,
        $lease,
        $externalPropertyId
    ) {
        $occupants = $lease->getOccupants();
        $externalUnitId = $lease->getExternalUnitId();
        $allContracts = [];
        /** @var Occupant $occupant */
        foreach ($occupants as $occupant) {
            $residentId =  $occupant->getOccuSeqNo();
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
        $this->logger->debug(
            sprintf(
                '[SyncBalance]%s contracts for processing' .
                ' by external property "%s" of holding "%s" #%d and leaseId (main resident Id) "%s"',
                $count ? 'Found ' . $count : 'Not found any',
                $externalPropertyId,
                $holding->getName(),
                $holding->getId(),
                $occupant->getResiId()
            )
        );

        return $allContracts;
    }

    /**
     * @param Contract|ContractWaiting $contract
     * @param Lease $lease
     * @throws \Exception
     */
    protected function updateContractBalanceForResidentTransaction(
        $contract,
        $lease
    ) {
        $disallow = $lease->getBlockPaymentAccess();
        if (strtolower($disallow) === 'y') {
            $disallow = PaymentAccepted::DO_NOT_ACCEPT;
        } else {
            $disallow = PaymentAccepted::ANY;
        }
        $balance = $lease->getEndBalance();
        $contract->setPaymentAccepted($disallow);
        $this->logger->info(
            sprintf(
                '[SyncBalance]Setup payment accepted to %s, for leaseId %s',
                $disallow,
                $lease->getResiId()
            )
        );
        $externalLeaseId = $contract->getExternalLeaseId();
        if (empty($externalLeaseId)) {
            $contract->setExternalLeaseId($lease->getResiId());
            $this->logger->info(
                sprintf(
                    '[SyncBalance]%s #%d externalLeaseId has been updated. ExternalLeaseId set to #%s',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId(),
                    $lease->getResiId()
                )
            );
        }
        $contract->setIntegratedBalance($balance);
        $this->logger->info(
            sprintf(
                '[SyncBalance]%s #%s has been updated. Now the balance is $%s',
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId(),
                $balance
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingRent()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingRentAMSI();
    }

    /**
     * @param Holding $holding
     * @param Lease $lease
     * @param string $externalPropertyId
     */
    protected function processingResidentForUpdateRent(
        Holding $holding,
        $lease,
        $externalPropertyId
    ) {
        $recurringCodes = $holding->getRecurringCodesArray();
        $sumRecurringCharges = $this->getSumRecurringCharges($lease, $recurringCodes);
        $leaseId = $lease->getResiId();
        $externalUnitId = $lease->getExternalUnitId();

        $allContracts = [];

        $contracts = $this
            ->getContractRepository()
            ->findContractsByHoldingExternalPropertyLeaseExternalUnitId(
                $holding,
                $externalPropertyId,
                $leaseId,
                $externalUnitId
            );

        empty($contracts) || $allContracts = array_merge($allContracts, $contracts);

        $contractsWaiting = $this
            ->getContractWaitingRepository()
            ->findContractsByHoldingExternalPropertyLeaseExternalUnitId(
                $holding,
                $externalPropertyId,
                $leaseId,
                $externalUnitId
            );

        empty($contractsWaiting) || $allContracts = array_merge($allContracts, $contractsWaiting);

        /** @var Contract|ContractWaiting $contract */
        foreach ($allContracts as $contract) {
            $contract->setRent($sumRecurringCharges);
            try {
                $this->em->flush();
            } catch (\Exception $e) {
                $this->handleException($e);
            }
            $this->logger->info(
                sprintf(
                    '[SyncRent]Rent for %s #%d updated to %s',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId(),
                    $contract->getRent()
                )
            );
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
