<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ResidentDataManagerInterface as ResidentDataManager;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DI\Service("base.contract_sync")
 */
abstract class AbstractContractSynchronizer
{
    const LOGGER_PREFIX = '';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OutputInterface
     */
    protected $outputLogger;

    /**
     * @var ResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param ResidentDataManager $residentDataManager
     */
    public function setResidentDataManager(ResidentDataManager $residentDataManager)
    {
        $this->residentDataManager = $residentDataManager;
    }

    /**
     * Execute synchronization balance
     */
    public function syncBalance()
    {
        $this->logMessage('[SyncBalance]Started');
        try {
            $iterableResult = $this->getHoldingsForUpdatingBalance();
            $counter = 0;
            /** @var Holding $holding */
            while ((list($holding) = $iterableResult->next()) !== false) {
                $counter++;
                $this->setExternalSettings($holding);
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]Processing holding "%s" #%d',
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                $this->updateBalancesForHolding($holding);
            }
            if ($counter === 0) {
                $this->logMessage('[SyncBalance]No data to update');
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
        $this->logMessage('[SyncBalance]Finished');
    }

    /**
     * Execute synchronization rent of contracts
     */
    public function syncRent()
    {
        $this->logMessage('[SyncRent]Started');
        try {
            $iterableResult = $this->getHoldingsForUpdatingRent();
            $counter = 0;
            /** @var Holding $holding */
            while ((list($holding) = $iterableResult->next()) !== false) {
                $counter++;
                $this->setExternalSettings($holding);
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Processing holding "%s" #%d',
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                $this->updateContractsRentForHolding($holding);
            }
            if ($counter === 0) {
                $this->logMessage('[SyncRent]No data to update');
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
        $this->logMessage('[SyncRent]Finished');
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
     * @param string $message
     * @param string $logLevel should be one of LogLevel constant
     * @see LogLevel
     */
    protected function logMessage($message, $logLevel = LogLevel::DEBUG)
    {
        $this->logger->log($logLevel, static::LOGGER_PREFIX . $message);

        if ($this->outputLogger instanceof OutputInterface) {
            $this->outputLogger->writeln(static::LOGGER_PREFIX . $message);
        }
    }

    /**
     * @param Holding $holding
     * @throws \RuntimeException
     */
    protected function updateBalancesForHolding($holding)
    {
        $externalPropertyRows = $this->getPropertyMappingRepository()->findUniqueExternalPropertyIdsByHolding($holding);
        foreach ($externalPropertyRows as $externalPropertyRow) {
            list($externalPropertyId) = array_values($externalPropertyRow);
            try {
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]Try processing external property with id "%s"',
                        $externalPropertyId
                    )
                );
                $residentTransactions = $this->residentDataManager->getResidentTransactions(
                    $externalPropertyId
                );
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]Find %d resident transactions for processing' .
                        ' by external property "%s" of holding "%s" #%d',
                        count($residentTransactions),
                        $externalPropertyId,
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                foreach ($residentTransactions as $resident) {
                    try {
                        $this->processingResidentForUpdateBalance($holding, $resident, $externalPropertyId);
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
                    /** There we clear entity manager b/c we have a lot of object for UnitOfWork processing */
                    $this->em->clear();
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
     * Method for processing resident, get contracts for it and updated balances for each
     *
     * @param Holding $holding
     * @param mixed $resident
     * @param string $externalPropertyId
     */
    protected function processingResidentForUpdateBalance(Holding $holding, $resident, $externalPropertyId)
    {
        $contracts = $this->getContractsForUpdatingBalance($holding, $resident, $externalPropertyId);
        foreach ($contracts as $contract) {
            $this->logMessage(
                sprintf(
                    '[SyncBalance]Processing %s #%d.',
                    (new \ReflectionObject($contract))->getShortName(),
                    $contract->getId()
                )
            );
            try {
                $this->updateContractBalanceForResidentTransaction($contract, $resident);
                $this->em->flush();
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
     * @param Holding $holding
     */
    protected function updateContractsRentForHolding($holding)
    {
        $externalPropertyRows = $this->getPropertyMappingRepository()->findUniqueExternalPropertyIdsByHolding($holding);
        foreach ($externalPropertyRows as $externalPropertyRow) {
            list($externalPropertyId) = array_values($externalPropertyRow);
            try {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Try processing external property with id "%s"',
                        $externalPropertyId
                    )
                );
                $residentTransactions = $this->residentDataManager->getResidentsWithRecurringCharges(
                    $externalPropertyId
                );
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Find %d resident transactions for processing' .
                        ' by external property "%s" of holding "%s" #%d',
                        count($residentTransactions),
                        $externalPropertyId,
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                foreach ($residentTransactions as $resident) {
                    try {
                        $this->processingResidentForUpdateRent($holding, $resident, $externalPropertyId);
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
                    /** There we clear entity manager b/c we have a lot of object for UnitOfWork processing */
                    $this->em->clear();
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
     * @return \CreditJeeves\DataBundle\Entity\HoldingRepository
     */
    protected function getHoldingRepository()
    {
        return $this->em->getRepository('DataBundle:Holding');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PropertyMappingRepository
     */
    protected function getPropertyMappingRepository()
    {
        return $this->em->getRepository('RjDataBundle:PropertyMapping');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PropertyRepository
     */
    protected function getPropertyRepository()
    {
        return $this->em->getRepository('RjDataBundle:Property');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ContractRepository
     */
    protected function getContractRepository()
    {
        return $this->em->getRepository('RjDataBundle:Contract');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ContractWaitingRepository
     */
    protected function getContractWaitingRepository()
    {
        return $this->em->getRepository('RjDataBundle:ContractWaiting');
    }

    /**
     * @param Holding $holding
     */
    abstract protected function setExternalSettings(Holding $holding);

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    abstract protected function getHoldingsForUpdatingBalance();

    /**
     * @param Holding $holding
     * @param mixed $resident
     * @param string $externalPropertyId
     * @return Contract[]|ContractWaiting[]
     */
    abstract protected function getContractsForUpdatingBalance(
        Holding $holding,
        $resident,
        $externalPropertyId
    );

    /**
     * @param Contract|ContractWaiting $contract
     * @param mixed $resident
     */
    abstract protected function updateContractBalanceForResidentTransaction($contract, $resident);

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    abstract protected function getHoldingsForUpdatingRent();

    /**
     * @param Holding $holding
     * @param mixed $resident
     * @param string $externalPropertyId
     */
    abstract protected function processingResidentForUpdateRent(
        Holding $holding,
        $resident,
        $externalPropertyId
    );
}
