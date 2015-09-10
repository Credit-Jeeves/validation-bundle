<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\PropertyMappingRepository;
use RentJeeves\ExternalApiBundle\Model\ResMan\Charge;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\Detail;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtServiceTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transactions;
use Symfony\Component\Console\Output\OutputInterface;

class ContractSynchronizer
{
    const COUNT_PROPERTIES_PER_SET = 20;
    const COUNT_CONTRACTS_FOR_FLUSH = 20;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OutputInterface
     */
    protected $outputLogger;

    /**
     * @param EntityManager $em
     * @param ResidentDataManager $residentDataManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, ResidentDataManager $residentDataManager, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->residentDataManager = $residentDataManager;
    }

    /**
     * Execute synchronization balance
     */
    public function syncBalance()
    {
        try {
            $holdings = $this->getHoldings();
            if (empty($holdings)) {
                return $this->logMessage('ResMan ResidentBalanceSynchronizer: No data to update');
            }

            foreach ($holdings as $holding) {
                $this->residentDataManager->setSettings($holding->getExternalSettings());
                $this->logMessage(
                    sprintf('ResMan ResidentBalanceSynchronizer start work with holding %s', $holding->getId())
                );
                $this->updateBalancesForHolding($holding);
            }
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    '(ResMan ResidentBalanceSynchronizer)Message: %s, File: %s, Line:%s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );

            return $this->logMessage($e->getMessage());
        }
    }

    /**
     * @param OutputInterface $outputLogger
     * @return self
     */
    public function usingOutput(OutputInterface $outputLogger)
    {
        $this->outputLogger = $outputLogger;

        return $this;
    }

    /**
     * @param Holding $holding
     * @throws \Exception
     */
    protected function updateBalancesForHolding(Holding $holding)
    {
        $propertyRepository = $this->em->getRepository('RjDataBundle:Property');
        $propertySets = ceil(
            $propertyRepository->countContractPropertiesByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );
        for ($offset = 1; $offset <= $propertySets; $offset++) {
            $properties = $propertyRepository->findContractPropertiesByHolding(
                $holding,
                $offset,
                self::COUNT_PROPERTIES_PER_SET
            );
            /** @var Property $property */
            foreach ($properties as $property) {
                $propertyMapping = $property->getPropertyMappingByHolding($holding);

                if (empty($propertyMapping)) {
                    throw new \Exception(
                        sprintf(
                            'PropertyID \'%s\', doesn\'t have external ID',
                            $property->getId()
                        )
                    );
                }

                $residentTransactions = $this->residentDataManager->getResidents(
                    $propertyMapping->getExternalPropertyId()
                );

                if ($residentTransactions) {
                    $this->logMessage(
                        sprintf(
                            'ResMan ResidentBalanceSynchronizer: Processing resident transactions for property %s of
                             holding %s',
                            $propertyMapping->getExternalPropertyId(),
                            $holding->getName()
                        )
                    );
                    $this->processResidentTransactions($residentTransactions, $holding, $property);
                    continue;
                }

                $this->logMessage(
                    sprintf(
                        'ERROR: Could not load resident transactions ResMan for property %s of holding %s',
                        $propertyMapping->getExternalPropertyId(),
                        $holding->getName()
                    )
                );
            }
            $this->em->flush();
            $this->em->clear();
        }
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding[]
     */
    protected function getHoldings()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalanceResMan();
    }

    /**
     * @param Holding $holding
     * @param Property $property
     * @param Customer $customer
     * @param RtCustomer $customerBase
     * @return null|Contract|ContractWaiting
     * @throws \Exception
     */
    protected function getContract(Holding $holding, Property $property, Customer $customer, RtCustomer $customerBase)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $residentId = $customer->getCustomerId();
        $unitName = $customerBase->getRtUnit()->getUnitId();
        $contracts = $contractRepo->findContractByHoldingPropertyResidentUnit(
            $holding,
            $property,
            $residentId,
            $unitName
        );

        if (count($contracts) > 1) {
            $this->logMessage(
                sprintf(
                    'Found more than one contract with property %s, unitName %s, residentId %s',
                    $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
                    $unitName,
                    $residentId
                )
            );

            return null;
        }

        if (count($contracts) == 1) {
            /** @var Contract $contract */
            $contract = reset($contracts);

            return $contract;
        }

        $contractWaiting = $this->em->getRepository('RjDataBundle:ContractWaiting')
            ->findByHoldingPropertyUnitResident($holding, $property, $unitName, $residentId);

        if ($contractWaiting) {
            $this->logMessage(sprintf('Return contract waiting ID: %s', $contractWaiting->getId()));

            return $contractWaiting;
        }

        $this->logMessage(
            sprintf(
                'Could not find contract with property %s, unitName %s, resident %s',
                $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
                $unitName,
                $residentId
            )
        );

        return null;
    }

    /**
     * @param array $residentTransactions
     * @param Holding $holding
     * @param Property $property
     * @throws \Exception
     */
    protected function processResidentTransactions(
        array $residentTransactions,
        Holding $holding,
        Property $property
    ) {
        /** @var RtCustomer $customerBase */
        foreach ($residentTransactions as $customerBase) {
            if ($customerBase->getCustomers()->getCustomer()->count() === 0) {
                continue;
            }
            /** @var Customer $customerUser */
            foreach ($customerBase->getCustomers()->getCustomer() as $customerUser) {
                $contract = $this->getContract($holding, $property, $customerUser, $customerBase);
                if (!$contract) {
                    continue;
                }

                $this->doUpdate($customerBase, $contract);
            }
        }
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdate(RtCustomer $baseCustomer, $contract)
    {
        $contract->setPaymentAccepted($baseCustomer->getRentTrackPaymentAccepted());
        $contract->setIntegratedBalance($baseCustomer->getRentTrackBalance());
        $externalLeaseId = $contract->getExternalLeaseId();
        if (empty($externalLeaseId)) {
            $contract->setExternalLeaseId($baseCustomer->getCustomerId());
        }
        $this->logMessage(
            sprintf(
                'ResMan: payment accepted to %s, balance %s.
                For ContractID: %s',
                $contract->getPaymentAccepted(),
                $contract->getIntegratedBalance(),
                $contract->getId()
            )
        );
    }

    /**
     * @param string $message
     */
    protected function logMessage($message)
    {
        $this->logger->debug($message);
        if ($this->outputLogger) {
            $this->outputLogger->writeln($message);
        }
    }

    public function syncRecurringCharge()
    {
        $holdings = $this->getHoldingRepository()->findHoldingsForResmanSyncRecurringCharges();
        if (empty($holdings)) {
            $this->logMessage('ResMan sync Recurring Charge: No data to update.');
        }

        foreach ($holdings as $holding) {
            $this->updateContractsRentForHolding($holding);
        }
    }

    /**
     * @param Holding $holding
     */
    protected function updateContractsRentForHolding(Holding $holding)
    {
        $this->logMessage(sprintf('ResMan sync Recurring Charge: start work with holding %d', $holding->getId()));

        $this->residentDataManager->setSettings($holding->getExternalSettings());
        $countPropertyMappingSets = ceil(
            $this->getPropertyMappingRepository()->getCountUniqueByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );

        $this->logMessage(sprintf('Found %d pages of property mappings', $countPropertyMappingSets));

        for ($offset = 1; $offset <= $countPropertyMappingSets; $offset++) {
            $this->logMessage(sprintf('ResMan sync Recurring Charge: Open %d page of property mappings', $offset));

            $propertyMappings = $this->getPropertyMappingRepository()->findUniqueByHolding(
                $holding,
                $offset,
                self::COUNT_PROPERTIES_PER_SET
            );

            $counter = 0;
            /** @var PropertyMapping $propertyMapping */
            foreach ($propertyMappings as $propertyMapping) {
                $this->updateContractsRentForPropertyMapping($propertyMapping);
                $counter++;
                if ($counter === self::COUNT_CONTRACTS_FOR_FLUSH) {
                    $this->em->flush();
                    $counter = 0;
                }
            }

            $this->em->flush();
            $this->em->clear();
        }
    }

    /**
     * @param PropertyMapping $propertyMapping
     */
    protected function updateContractsRentForPropertyMapping(PropertyMapping $propertyMapping)
    {
        $this->logMessage(
            sprintf(
                'ResMan sync Recurring Charge: start work with propertyMapping \'%s\'',
                $propertyMapping->getExternalPropertyId()
            )
        );

        try {
            $rtResidentTransactions = $this->residentDataManager->getRTServiceTransactionsWithRecurringCharges(
                $propertyMapping->getExternalPropertyId()
            );
        } catch (\Exception $e) {
            $this->logMessage(
                sprintf(
                    'ResMan sync Recurring Charge: \'%s\'',
                    $e->getMessage()
                ),
                500
            );

            return;
        }

        if (false == $rtResidentTransactions) {
            $this->logMessage(
                sprintf(
                    'ResMan sync Recurring Charge: ERROR:
                    Could not load resident transactions for Property %s of Holding#%d',
                    $propertyMapping->getExternalPropertyId(),
                    $propertyMapping->getHolding()->getId()
                ),
                500
            );

            return;
        }

        foreach ($rtResidentTransactions as $rtResidentTransaction) {
            $this->updateContractsRentForRTServiceTransactions($propertyMapping, $rtResidentTransaction);
        }
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param RtServiceTransactions $rtServiceTransaction
     */
    protected function updateContractsRentForRTServiceTransactions(
        PropertyMapping $propertyMapping,
        RtServiceTransactions $rtServiceTransaction
    ) {
        $this->logMessage('ResMan sync Recurring Charge: Searching for contracts.');

        if (null === $contract = $this->getContractForSyncRecurringCharges($propertyMapping, $rtServiceTransaction)) {
            return;
        }

        $this->logMessage(
            sprintf(
                'ResMan sync Recurring Charge: start work with %s#%d.',
                get_class($contract),
                $contract->getId()
            )
        );

        $recurringCodes = $propertyMapping->getHolding()->getRecurringCodes();
        if (empty($recurringCodes)) {
            $recurringCodes = [];
        } else {
            $recurringCodes = explode(',', $propertyMapping->getHolding()->getRecurringCodes());
        }

        $sumRecurringCharges = $this->getSumRecurringCharges($rtServiceTransaction, $recurringCodes);

        if ($sumRecurringCharges <= 0) {
            $this->logMessage(
                sprintf(
                    'ResMan sync Recurring Charge: ERROR: sum of RecurringCharges for contract(%d) = %d',
                    $contract->getId(),
                    $sumRecurringCharges
                ),
                500
            );

            return;
        }

        $contract->setRent($sumRecurringCharges);
        $this->logMessage(
            sprintf(
                'ResMan sync Recurring Charge: Rent for Contract#%d updated',
                $contract->getId()
            )
        );
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

    /**
     * @param PropertyMapping $propertyMapping
     * @param RtServiceTransactions $rtServiceTransaction
     *
     * @return null|Contract|ContractWaiting
     */
    protected function getContractForSyncRecurringCharges(
        PropertyMapping $propertyMapping,
        RtServiceTransactions $rtServiceTransaction
    ) {
        $firstDetailFortServiceTransaction = $this->getFirstDetailForRTServiceTransaction($rtServiceTransaction);
        if (null === $firstDetailFortServiceTransaction) {
            $this->logMessage(
                'ResMan sync Recurring Charge: ERROR: rtServiceTransaction does not have details.',
                500
            );

            return null;
        }

        $contracts = $this->getContractRepository()->findContractByHoldingPropertyResidentUnit(
            $propertyMapping->getHolding(),
            $propertyMapping->getProperty(),
            $firstDetailFortServiceTransaction->getCustomerId(),
            $firstDetailFortServiceTransaction->getUnitID()
        );

        if (count($contracts) > 1) {
            $this->logMessage(
                sprintf(
                    'ResMan sync Recurring Charge:
                    Found more than one contract with property %s, unitId %s, residentId %s',
                    $propertyMapping->getExternalPropertyId(),
                    $firstDetailFortServiceTransaction->getUnitID(),
                    $firstDetailFortServiceTransaction->getCustomerId()
                )
            );

            return null;
        }

        if (count($contracts) === 1) {
            /** @var Contract $contract */
            $contract = reset($contracts);

            return $contract;
        }

        $contractWaiting = $this->em->getRepository('RjDataBundle:ContractWaiting')
            ->findByHoldingPropertyUnitResident(
                $propertyMapping->getHolding(),
                $propertyMapping->getProperty(),
                $firstDetailFortServiceTransaction->getUnitID(),
                $firstDetailFortServiceTransaction->getCustomerId()
            );

        if (false == $contractWaiting) {
            $this->logMessage(
                sprintf(
                    'ResMan sync Recurring Charge:
                    Could not find Contract or contractWaiting with property %s, unitName %s, resident %s',
                    $propertyMapping->getExternalPropertyId(),
                    $firstDetailFortServiceTransaction->getUnitID(),
                    $firstDetailFortServiceTransaction->getCustomerId()
                )
            );

            return null;
        }

        return $contractWaiting;
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
     * @return \CreditJeeves\DataBundle\Entity\HoldingRepository
     */
    protected function getHoldingRepository()
    {
        return $this->em->getRepository('DataBundle:Holding');
    }

    /**
     * @return PropertyMappingRepository
     */
    protected function getPropertyMappingRepository()
    {
        return $this->em->getRepository('RjDataBundle:PropertyMapping');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ContractRepository
     */
    protected function getContractRepository()
    {
        return $this->em->getRepository('RjDataBundle:Contract');
    }
}
