<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Helpers\DateChecker;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ExternalApiBundle\Services\AbstractContractSynchronizer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionTransactions;

/**
 * DI\Service("yardi.contract_sync")
 */
class ContractSynchronizer extends AbstractContractSynchronizer
{
    const LOGGER_PREFIX = '[Yardi ContractSynchronizer]';

    /**
     * {@inheritdoc}
     */
    protected function setExternalSettings(Holding $holding)
    {
        $this->residentDataManager->setSettings($holding->getYardiSettings());
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingBalance()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingBalanceYardi();
    }

    /**
     * @param Holding $holding
     * @param ResidentTransactionPropertyCustomer $resident
     * @param string $externalPropertyId
     * @return Contract[]|ContractWaiting[]
     */
    protected function getContractsForUpdatingBalance(
        Holding $holding,
        $resident,
        $externalPropertyId
    ) {
        /** @var Customer[] $roommates */
        $roommates = $resident->getCustomers()->getCustomer();

        $allContracts = [];

        foreach ($roommates as $roommate) {
            $residentId = $roommate->getCustomerId();
            $unitName = $resident->getUnit()->getUnitId();
            $contracts = $this
                ->getContractRepository()
                ->findContractsByHoldingExternalPropertyResidentUnit(
                    $holding,
                    $externalPropertyId,
                    $residentId,
                    $unitName
                );
            empty($contracts) || $allContracts = array_merge($allContracts, $contracts);

            $contractsWaiting = $this
                ->getContractWaitingRepository()
                ->findContractsByHoldingExternalPropertyResidentUnit(
                    $holding,
                    $externalPropertyId,
                    $residentId,
                    $unitName
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
     * @param ResidentTransactionPropertyCustomer $resident
     * @throws \Exception
     */
    protected function updateContractBalanceForResidentTransaction(
        $contract,
        $resident
    ) {
        $balance = $this->calcResidentBalance($resident);
        $contract->setPaymentAccepted($resident->getPaymentAccepted());
        $this->logMessage(
            sprintf(
                '[SyncBalance]Setup payment accepted to %s, for leaseId %s',
                $resident->getPaymentAccepted(),
                $resident->getCustomerId()
            )
        );
        $externalLeaseId = $contract->getExternalLeaseId();
        if (empty($externalLeaseId)) {
            $contract->setExternalLeaseId($resident->getLeaseId());
            $this->logMessage(
                sprintf(
                    '[SyncBalance]%s #%d externalLeaseId has been updated. ExternalLeaseId set to #%s',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId(),
                    $resident->getLeaseId()
                )
            );
        }
        $contract->setIntegratedBalance($balance);
        $this->logMessage(
            sprintf(
                '[SyncBalance]%s #%s has been updated. Now the balance is $%s',
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId(),
                $balance
            )
        );
    }

    /**
     * @param ResidentTransactionPropertyCustomer $resident
     * @return int
     */
    protected function calcResidentBalance(ResidentTransactionPropertyCustomer $resident)
    {
        $balance = 0;
        /** @var ResidentTransactionTransactions[] $transactions */
        $transactions = $resident->getServiceTransactions() ?
                $resident->getServiceTransactions()->getTransactions() : [];

        foreach ($transactions as $transaction) {
            $balance += $transaction->getCharge() ? $transaction->getCharge()->getDetail()->getBalanceDue() : 0;
        }

        return $balance;
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingRent()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingRentYardi();
    }

    protected function processingResidentForUpdateRent(
        Holding $holding,
        $resident,
        $externalPropertyId
    ) {
        $recurringCodes = $holding->getRecurringCodesArray();
        $serviceTransactions = $resident->getServiceTransactions();
        /** @var ResidentTransactionTransactions[] $transactions */
        $transactions = $serviceTransactions->getTransactions();
        $amount = 0;
        foreach ($transactions as $transaction) {
            $charge = $transaction->getCharge();
            if (!in_array($charge->getDetail()->getChargeCode(), $recurringCodes) && !empty($recurringCodes)) {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]RecurringCodes list(%s) does not contain charge code (%s)',
                        $holding->getRecurringCodes(),
                        $charge->getDetail()->getChargeCode()
                    )
                );
                continue;
            }
            $fromDate = $charge->getDetail()->getServiceFromDateObject();
            $toDate = $charge->getDetail()->getServiceToDateObject();
            if (!DateChecker::nowFallsBetweenDates($fromDate, $toDate)) {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Today does not fall between "%s" and "%s"',
                        $fromDate ? $fromDate->format('Y-m-d') : '',
                        $toDate ? $toDate->format('Y-m-d') : ''
                    )
                );
                continue;
            }
            $leaseId = $charge->getDetail()->getCustomerID();
            $unitName = $charge->getDetail()->getUnitID();
            $amount += $charge->getDetail()->getAmount();
        }

        if (empty($leaseId) || empty($unitName)) {
            throw new \LogicException(
                sprintf(
                    '[SyncRent]Lease id and unitName can not be empty for external property "%s"',
                    $externalPropertyId
                )
            );
        }

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
            $contract->setRent($amount);
            try {
                $this->em->flush();
            } catch (\Exception $e) {
                $this->handleException($e);
            }
            $this->logMessage(
                sprintf(
                    '[SyncRent]Rent for %s #%d was set to %s',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId(),
                    $contract->getRent()
                )
            );
        }
    }
}
