<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Helpers\DateChecker;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ExternalApiBundle\Model\MRI\Charge;
use RentJeeves\ExternalApiBundle\Model\MRI\Resident;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ExternalApiBundle\Services\AbstractContractSynchronizer;

/**
 * DI\Service("mri.contract_sync")
 */
class ContractSynchronizer extends AbstractContractSynchronizer
{
    /**
     * {@inheritdoc}
     */
    protected function setExternalSettings(Holding $holding)
    {
        $this->residentDataManager->setSettings($holding->getMriSettings());
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingBalance()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingBalanceMRI();
    }

    /**
     * @param Holding $holding
     * @param Value $resident
     * @param string $externalPropertyId
     * @return Contract[]
     */
    protected function getContractsForUpdatingBalance(
        Holding $holding,
        $resident,
        $externalPropertyId
    ) {
        if ($this->isResidentOutOfDate($resident)) {
            return [];
        }

        $residentId = $resident->getResidentId();
        $externalUnitId = $resident->getExternalUnitId();

        $allContracts = $this->getContracts($holding, $externalPropertyId, $residentId, $externalUnitId);
        $count = count($allContracts);
        $this->logger->debug(
            sprintf(
                '[SyncBalance]%s contracts for processing' .
                ' by external property "%s" of holding "%s" #%d and leaseId (main resident Id) "%s"',
                $count ? 'Found ' . $count : 'Not found any',
                $externalPropertyId,
                $holding->getName(),
                $holding->getId(),
                $resident->getLeaseId()
            )
        );

        return $allContracts;
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @param string $residentId
     * @param string $externalUnitId
     * @return Contract[]
     */
    protected function getContracts(Holding $holding, $externalPropertyId, $residentId, $externalUnitId)
    {
        $allContracts = [];
        $contracts = $this
            ->getContractRepository()
            ->findContractsByHoldingExternalPropertyResidentExternalUnitId(
                $holding,
                $externalPropertyId,
                $residentId,
                $externalUnitId
            );

        empty($contracts) || $allContracts = array_merge($allContracts, $contracts);

        return $allContracts;
    }

    /**
     * @param Contract $contract
     * @param Value $resident
     * @throws \Exception
     */
    protected function updateContractBalanceForResidentTransaction(
        Contract $contract,
        $resident
    ) {
        $contract->setPaymentAccepted($resident->getPaymentAccepted());
        $this->logger->info(
            sprintf(
                '[SyncBalance]Setup payment accepted to %s, for leaseId %s',
                $resident->getPaymentAccepted(),
                $resident->getLeaseId()
            )
        );

        $externalLeaseId = $contract->getExternalLeaseId();
        if (empty($externalLeaseId)) {
            $contract->setExternalLeaseId($resident->getLeaseId());
            $this->logger->info(
                sprintf(
                    '[SyncBalance]%s #%d externalLeaseId has been updated. ExternalLeaseId set to #%s',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId(),
                    $resident->getLeaseId()
                )
            );
        }

        $contract->setIntegratedBalance($resident->getLeaseBalance());
        $this->logger->info(
            sprintf(
                '[SyncBalance]%s #%s has been updated. Now the balance is $%s',
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId(),
                $resident->getLeaseBalance()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingRent()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingRentMRI();
    }

    /**
     * @param Holding $holding
     * @param Value $customer
     * @param string $externalPropertyId
     */
    protected function processingResidentForUpdateRent(
        Holding $holding,
        $customer,
        $externalPropertyId
    ) {
        if ($this->isResidentOutOfDate($customer)) {
            return;
        }
        $sumRecurringCharges = $this->getSumRecurringCharges($customer, $holding->getRecurringCodesArray());

        /** @var Resident $resident */
        foreach ($customer->getResidents()->getResidentArray() as $resident) {
            $residentId = $resident->getResidentId();
            $externalUnitId = $customer->getExternalUnitId();
            $allContracts = $this->getContracts($holding, $externalPropertyId, $residentId, $externalUnitId);
            $count = count($allContracts);
            $this->logger->debug(
                sprintf(
                    '[SyncRent]%s contracts for processing' .
                    ' by external property "%s" of holding "%s" #%d and leaseId (main resident Id) "%s"',
                    $count ? 'Found ' . $count : 'Not found any',
                    $externalPropertyId,
                    $holding->getName(),
                    $holding->getId(),
                    $residentId
                )
            );
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
    }

    /**
     * @param Value $customer
     * @param array $recurringCodes
     *
     * @return int
     */
    protected function getSumRecurringCharges(Value $customer, array $recurringCodes)
    {
        $currentCharges = $customer->getCurrentCharges();
        $charges = $currentCharges->getCharges();
        $amount = 0;
        /** @var Charge $charge */
        foreach ($charges as $charge) {
            if (strtolower($charge->getFrequency()) !== 'm') {
                $this->logger->info(sprintf('[SyncRent]Frequency not equals "m" it "%s"', $charge->getFrequency()));
                continue;
            }

            $chargeCode = $charge->getChargeCode();
            if (!in_array($chargeCode, $recurringCodes) && !empty($chargeCodes)) {
                $this->logger->info(
                    sprintf(
                        '[SyncRent]Charge code(%s) not in list (%s)',
                        $chargeCode,
                        explode(', ', $recurringCodes)
                    )
                );
                continue;
            }

            $effectiveDate = $charge->getDateTimeEffectiveDate();
            $endDate = $charge->getDateTimeEndDate();

            if (!DateChecker::nowFallsBetweenDates($effectiveDate, $endDate)) {
                $this->logger->info(
                    sprintf(
                        '[SyncRent]Today doesn\'t not fall between "%s" and "%s"',
                        $effectiveDate ? $effectiveDate->format('Y-m-d') : '',
                        $endDate ? $endDate->format('Y-m-d') : ''
                    )
                );
                continue;
            }

            $amount += $charge->getAmount();
        }

        return $amount;
    }

    /**
     * @param Value $resident
     * @return bool
     */
    protected function isResidentOutOfDate(Value $resident)
    {
        $moveOut = $resident->getLeaseMoveOut();
        $threeMonthAgo = new \DateTime('-3 month');

        if (strtoupper($resident->getIsCurrent()) !== 'Y' && $moveOut && ($moveOut <= $threeMonthAgo)) {
            return true;
        }

        return false;
    }
}
