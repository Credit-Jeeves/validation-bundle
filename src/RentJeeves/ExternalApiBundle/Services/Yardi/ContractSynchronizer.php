<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use Psr\Log\LogLevel;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Services\AbstractContractSynchronizer;
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
     * @param ResidentTransactionPropertyCustomer $resident
     * @param PropertyMapping $propertyMapping
     * @throws \Exception
     */
    protected function updateContractBalanceForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    ) {
        $residentId = $resident->getCustomerId();
        $unitName = $resident->getUnit()->getUnitId();
        $contract = $this->getContract($propertyMapping, $residentId, $unitName);
        if (!$contract) {
            $this->logMessage(
                sprintf(
                    '[SyncBalance]Contract not found for property mapping #%d, resident "%s" and unit name "%s"',
                    $propertyMapping->getId(),
                    $residentId,
                    $unitName
                )
            );

            return;
        }
        $balance = $this->calcResidentBalance($resident);
        $contract->setPaymentAccepted($resident->getPaymentAccepted());
        $this->logMessage(
            sprintf(
                '[SyncBalance]Setup payment accepted to %s, for residentId %s',
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

        if (empty($residentId) || empty($unitName)) {
            return;
        }

        $contracts = $this->getContractsByLeaseId($propertyMapping, $residentId, $unitName);

        if (empty($contracts)) {
            $this->logger->info('Yardi sync Recurring Charge: empty contract.');

            return;
        }

        foreach ($contracts as $contract) {
            if ($amount === 0) {
                $this->logger->error(
                    sprintf(
                        'Yardi sync Recurring Charge: ERROR: sum of RecurringCharges for contract #%s = 0',
                        $contract->getId()
                    )
                );
                continue;
            }
            $this->logger->info(
                sprintf(
                    'Yardi sync Recurring Charge: set new rent %s for contract #%s',
                    $amount,
                    $contract->getId()
                )
            );
            $contract->setRent($amount);
        }
    }

    /**
     * @param Holding $holding
     * @throws Exception
     */
    protected function updateBalancesForHolding(Holding $holding)
    {
        $repo = $this->em->getRepository('RjDataBundle:Property');

        /** @var $residentClient ResidentTransactionsClient */
        $residentClient = $this->clientFactory->getClient(
            $holding->getYardiSettings(),
            SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS
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
     * @param PropertyMapping $propertyMapping
     * @param string $residentId
     * @param string $unitName
     * @return Contract
     * @throws \Exception
     * @return Contract[]
     * @throws Exception
     */
    protected function getContractsByResidentId(PropertyMapping $propertyMapping, $residentId, $unitName)
    {
        $holding = $propertyMapping->getHolding();
        $property = $propertyMapping->getProperty();
        $this->logMessage(
            sprintf(
                'Searching contract by residentId:%s, property:%s, holding:%s, unitName:%s',
                $residentId,
                $property->getId(),
                $holding->getId(),
                $unitName
            )
        );

        $contracts = $contractRepo->findContractByHoldingPropertyResident(
            $holding,
            $property,
            $residentId
        );

        $contracts = $this->processContracts($contracts, $unitName);
        if ($contracts) {
            return $contracts;
        $contracts = $this
            ->getContractRepository()
            ->findContractByHoldingPropertyResident($holding, $property, $residentId);
        if ($contract = $this->processContracts($contracts, $unitName)) {
            return $contract;
        }

        $contractsWaiting = $this
            ->getContractWaitingRepository()
            ->findByHoldingPropertyResident($holding, $property, $residentId);
        if ($contractWaiting = $this->processContracts($contractsWaiting, $unitName)) {
            return $contractWaiting;
        $contractsWaiting = $this->processContracts($contractsWaiting, $unitName);

        if ($contractsWaiting) {
            return $contractsWaiting;
        }

        return null;
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param string $externalLeaseId
     * @param string $unitName
     * @return Contract[]
     * @throws Exception
     */
    protected function getContractsByLeaseId(PropertyMapping $propertyMapping, $externalLeaseId, $unitName)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $holding = $propertyMapping->getHolding();
        $property = $propertyMapping->getProperty();
        $this->logger->info(
            sprintf(
                'Start Search contract by externalLeaseId:%s, property:%s, holding:%s, unitName:%s',
                $externalLeaseId,
                $property->getId(),
                $holding->getId(),
                $unitName
            )
        );

        $contracts = $contractRepo->findContractByHoldingPropertyLeaseId(
            $holding,
            $property,
            $externalLeaseId
        );

        $contracts = $this->processContracts($contracts, $unitName);
        if ($contracts) {
            return $contracts;
        }

        $contractsWaiting = $this->em->getRepository('RjDataBundle:ContractWaiting')
            ->findByHoldingPropertyUnitExternalLeaseId($holding, $property, $unitName, $externalLeaseId);
        $contractsWaiting = $this->processContracts($contractsWaiting, $unitName);

        if ($contractsWaiting) {
            return $contractsWaiting;
        }

        return null;
    }

    /**
     * @param Contract[]|ContractWaiting[] $contracts
     * @param string $unitName
     * @return null|Contract|ContractWaiting
     */
    protected function processContracts(array $contracts, $unitName)
    {
        if (count($contracts) === 1) {
            $contract = reset($contracts);
            $this->logMessage(
                sprintf(
                    'Found %s with ID:%d',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId()
                )
            );

            return $contracts;
        }

        if (count($contracts) > 1) {
            $this->logger->info('Found more than 1 contract for this parameters');
            $matchedContract = [];
            $this->logMessage('Found more than one contract for this parameters');
            foreach ($contracts as $contract) {
                $unit = $contract->getUnit();
                if ($unit->getName() === $unitName) {
                    $this->logger->info(
                        sprintf(
                            'Found contract with ID:%s.',
                            $contract->getId()
                        )
                    );

                    $matchedContract[] = $contract;
                }
            }
            if (empty($matchedContract)) {
                $this->logger->alert(sprintf('YardiBalanceSync: Contract with unitName %s not found.', $unitName));
            } else {
                return $matchedContract;
            }
            $this->logMessage(
                sprintf('ERROR: Contract with unitName %s not found.', $unitName),
                LogLevel::ALERT
            );
        }

        return null;
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
        return $this->getHoldingRepository()->findHoldingsForSyncRecurringChargesYardi();
    }

    /**
     * @param ResidentTransactionPropertyCustomer $resident
     * @param PropertyMapping $propertyMapping
     */
    protected function updateContractRentForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    ) {
        $recurringCodes = $propertyMapping->getHolding()->getRecurringCodesArray();
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
                        $propertyMapping->getHolding()->getRecurringCodes(),
                        $charge->getDetail()->getChargeCode()
                    )
                );
                continue;
            }

            if (!$this->checkDateFallsBetweenDates(
                $charge->getDetail()->getServiceFromDateObject(),
                $charge->getDetail()->getServiceToDateObject()
            )) {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Today doesn\'t not fall between "%s" and "%s"',
                        $charge->getDetail()->getServiceFromDateObject() ?
                            $charge->getDetail()->getServiceFromDateObject()->format('Y-m-d') :
                                '',
                        $charge->getDetail()->getServiceToDateObject() ?
                            $charge->getDetail()->getServiceToDateObject()->format('Y-m-d') :
                                ''
                    )
                );
                continue;
            }

            $residentId = $charge->getDetail()->getCustomerID();
            $unitName = $charge->getDetail()->getUnitID();
            $amount += $charge->getDetail()->getAmount();
        }

        if (empty($residentId) || empty($unitName)) {
            return;
        }

        $contract = $this->getContract($propertyMapping, $residentId, $unitName);

        if (!$contract) {
            $this->logMessage(
                sprintf(
                    '[SyncRent]Contract not found for property mapping #%d, resident "%s" and unit name "%s"',
                    $propertyMapping->getId(),
                    $residentId,
                    $unitName
                )
            );

            return;
        }

        if ($amount == 0) {
            throw new \LogicException(sprintf('Sum of RecurringCharges for contract #%d <= 0', $contract->getId()));
        }

        $contract->setRent($amount);
        $this->logMessage(
            sprintf(
                '[SyncRent]Rent for %s #%d updated to %s',
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId(),
                $amount
            )
        );
        $residents = $residentTransactions->getProperty()->getCustomers();
        foreach ($residents as $resident) {
            $this->logger->info(
                sprintf(
                    'YardiBalanceSync: Working with external lease id %s',
                    $resident->getCustomerId()
                )
            );
            $roommates = $resident->getCustomers()->getCustomer();
            foreach ($roommates as $roommate) {
                $residentId = $roommate->getCustomerId();
                $unitName = $resident->getUnit()->getUnitId();
                $contracts = $this->getContractsByResidentId($propertyMapping, $residentId, $unitName);
                if (!$contracts) {
                    continue;
                }

                foreach ($contracts as $contract) {
                    $balance = $this->calcResidentBalance($resident);
                    $contract->setPaymentAccepted($resident->getPaymentAccepted());
                    $this->logger->info(
                        sprintf(
                            'YardiBalanceSync: Setup payment accepted to %s, for residentId %s',
                            $resident->getPaymentAccepted(),
                            $resident->getCustomerId()
                        )
                    );
                    $contract->setIntegratedBalance($balance);
                    $externalLeaseId = $contract->getExternalLeaseId();
                    if (empty($externalLeaseId)) {
                        $contract->setExternalLeaseId($resident->getLeaseId());
                        $this->logger->info(
                            sprintf(
                                'Contract #%s externalLeaseId has been updated. ExternalLeaseId #%s',
                                $contract->getId(),
                                $resident->getLeaseId()
                            )
                        );
                    }
                    $this->logger->info(
                        sprintf(
                            'Contract #%s has been updated. Now the balance is $%s',
                            $contract->getId(),
                            $balance
                        )
                    );
                }
            }
        }
    }

    /**
     * @param ResidentTransactionPropertyCustomer $resident
     * @return int
     */
    protected function calcResidentBalance(ResidentTransactionPropertyCustomer $resident)
    {
        $balance = 0;
        $transactions = $resident->getServiceTransactions()->getTransactions();
        /** @var ResidentTransactionTransactions $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getCharge()) {
                $balanceDue = $transaction->getCharge()->getDetail()->getBalanceDue();
            } elseif ($transaction->getPayment()) {
                $balanceDue = $transaction->getPayment()->getDetail()->getAmount() * -1;
            } else {
                $balanceDue = 0;
            }
            $balance += $balanceDue;
        }

        return $balance;
    }

    /**
     * @TODO the same in MRI contract sync, merge this method when moving to abstract class the same code
     *
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return boolean
     */
    protected function checkDateFallsBetweenDates(\DateTime $startDate = null, \DateTime $endDate = null)
    {
        $today = new \DateTime();
        $todayStr = (int) $today->format('Ymd');
        //both parameter provider
        if (($startDate instanceof \DateTime && $endDate instanceof \DateTime) &&
            (int) $startDate->format('Ymd') <= $todayStr && (int) $endDate->format('Ymd') >= $todayStr
        ) {
            return true;
        }

        //only startDate parameter provider
        if (($startDate instanceof \DateTime && !($endDate instanceof \DateTime)) &&
            (int) $startDate->format('Ymd') <= $todayStr
        ) {
            return true;
        }

        //only endDate parameter provider
        if ((!($startDate instanceof \DateTime) && $endDate instanceof \DateTime) &&
            (int) $endDate->format('Ymd') >= $todayStr
        ) {
            return true;
        }

        if (empty($startDate) && empty($endDate)) {
            return true;
        }

        return false;
    }
}
