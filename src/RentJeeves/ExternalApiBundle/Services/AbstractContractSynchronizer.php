<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
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
        $iterableResult = $this->getPropertyMappingRepository()->findUniqueByHolding($holding);
        /** @var PropertyMapping $propertyMapping */
        while ((list($propertyMapping) = $iterableResult->next()) !== false) {
            try {
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]Try processing external property with id "%s"',
                        $propertyMapping->getExternalPropertyId()
                    )
                );
                $residentTransactions = $this->residentDataManager->getResidentTransactions(
                    $propertyMapping->getExternalPropertyId()
                );
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]Find %d resident transactions for processing' .
                        ' by external property "%s" of holding "%s" #%d',
                        count($residentTransactions),
                        $propertyMapping->getExternalPropertyId(),
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                foreach ($residentTransactions as $resident) {
                    $this->updateContractBalanceForResidentTransaction($resident, $propertyMapping);
                    $this->em->flush();
                }
                /** There we clear entity manager b/c we have a lot of object for UnitOfWork processing */
                $this->em->clear();
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
        $iterableResult = $this->getPropertyMappingRepository()->findUniqueByHolding($holding);
        /** @var PropertyMapping $propertyMapping */
        while ((list($propertyMapping) = $iterableResult->next()) !== false) {
            try {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Try processing property mapping #%d with external id "%s"',
                        $propertyMapping->getId(),
                        $propertyMapping->getExternalPropertyId()
                    )
                );
                $residentTransactions = $this->residentDataManager->getResidentsWithRecurringCharges(
                    $propertyMapping->getExternalPropertyId()
                );
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]Find %d resident transactions for processing by property %d of holding %s #%d',
                        count($residentTransactions),
                        $propertyMapping->getProperty()->getId(),
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                foreach ($residentTransactions as $resident) {
                    $this->updateContractRentForResidentTransaction($resident, $propertyMapping);
                    $this->em->flush();
                }
                /** There we clear entity manager b/c we have a lot of object for UnitOfWork processing */
                $this->em->clear();
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
     * @param mixed $resident
     * @param PropertyMapping $propertyMapping
     */
    abstract protected function updateContractBalanceForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    );

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    abstract protected function getHoldingsForUpdatingRent();

    /**
     * @param mixed $resident
     * @param PropertyMapping $propertyMapping
     */
    abstract protected function updateContractRentForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    );
}
