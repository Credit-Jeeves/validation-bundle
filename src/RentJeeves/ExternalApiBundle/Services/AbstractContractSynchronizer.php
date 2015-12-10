<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\ExternalApiBundle\Command\SyncContractBalanceCommand;
use RentJeeves\ExternalApiBundle\Command\SyncContractRentCommand;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ResidentDataManagerInterface as ResidentDataManager;

/**
 * DI\Service("base.contract_sync")
 */
abstract class AbstractContractSynchronizer implements ContractSynchronizerInterface
{
    const SYNC_BALANCE = 'balance';

    const SYNC_RENT = 'rent';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * {@inheritdoc}
     */
    public function syncBalance()
    {
        $this->logger->info('[SyncBalance]Started');
        try {
            $iterableResult = $this->getHoldingsForUpdatingBalance();
            $counter = 0;
            /** @var Holding $holding */
            while ((list($holding) = $iterableResult->next()) !== false) {
                $counter++;
                $this->logger->info(
                    sprintf(
                        '[SyncBalance]Processing holding "%s" #%d',
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                $this->updateBalancesForHolding($holding);
            }
            if ($counter === 0) {
                $this->logger->warn('[SyncBalance]No data to update');
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        $this->logger->info('[SyncBalance]Finished');
    }

    /**
     * {@inheritdoc}
     */
    public function syncBalanceForHoldingAndExternalPropertyId(Holding $holding, $externalPropertyId)
    {
        $this->logger->info(
            sprintf(
                '[SyncBalance]Start synchronizing balance for holding: %s(#%d) and external property: %s',
                $holding->getName(),
                $holding->getId(),
                $externalPropertyId
            )
        );
        try {
            $this->setExternalSettings($holding);
            $residentTransactions = $this->residentDataManager->getResidentTransactions(
                $externalPropertyId
            );
            $this->logger->info(
                sprintf(
                    '[SyncBalance]Found %d resident transactions for processing' .
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
                    $this->handleException($e);
                }
                /** There we clear entity manager b/c we have a lot of object for UnitOfWork processing */
                $this->em->clear();
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        $this->logger->info(
            sprintf(
                '[SyncBalance]Finished synchronizing balance for holding: %s(#%d) and external property: %s',
                $holding->getName(),
                $holding->getId(),
                $externalPropertyId
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function syncRent()
    {
        $this->logger->info('[SyncRent]Started');
        try {
            $iterableResult = $this->getHoldingsForUpdatingRent();
            $counter = 0;
            /** @var Holding $holding */
            while ((list($holding) = $iterableResult->next()) !== false) {
                $counter++;
                $this->logger->info(
                    sprintf(
                        '[SyncRent]Processing holding "%s" #%d',
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                $this->updateContractsRentForHolding($holding);
            }
            if ($counter === 0) {
                $this->logger->warn('[SyncRent]No data to update');
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        $this->logger->info('[SyncRent]Finished');
    }

    /**
     * {@inheritdoc}
     */
    public function syncRentForHoldingAndExternalPropertyId(Holding $holding, $externalPropertyId)
    {
        $this->logger->info(
            sprintf(
                '[SyncRent]Start synchronizing rent for holding: %s(#%d) and external property: %s',
                $holding->getName(),
                $holding->getId(),
                $externalPropertyId
            )
        );
        try {
            $this->setExternalSettings($holding);
            $residentTransactions = $this->residentDataManager->getResidentsWithRecurringCharges(
                $externalPropertyId
            );
            $this->logger->info(
                sprintf(
                    '[SyncRent]Found %d resident transactions for processing' .
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
                    $this->handleException($e);
                }
                /** There we clear entity manager b/c we have a lot of object for UnitOfWork processing */
                $this->em->clear();
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        $this->logger->info(
            sprintf(
                '[SyncRent]Finished synchronizing rent for holding: %s(#%d) and external property: %s',
                $holding->getName(),
                $holding->getId(),
                $externalPropertyId
            )
        );
    }

    /**
     * Reconnect to DB connection if it is closed.
     *
     * The connection can be closed when an exception the thrown from the DB layer. In these cases,
     * we need to re-connect the EntityManger to the DB
     *
     * @throws \Doctrine\ORM\ORMException
     */
    protected function reConnectDB()
    {
        if (!$this->em->isOpen()) {
            $this->em = $this->em->create(
                $this->em->getConnection(),
                $this->em->getConfiguration()
            );
        }
    }

    /**
     * @param \Exception $e
     */
    protected function handleException(\Exception $e)
    {
        $message = sprintf('ERROR: %s on %s:%d', $e->getMessage(), $e->getFile(), $e->getLine());
        try {
            $this->logger->alert($message);
        } catch (\Exception $e) {
            // todo: swift is giving us "fwrite(): send of 108 bytes failed with errno=32 Broken pipe" errors
            // For reason why, please see https://credit.atlassian.net/browse/RT-1733
            // For now, we need to keep going to sync all the contracts that we can.
            $this->logger->error(
                sprintf(
                    'ERROR: cannot send alert email:"%s". Exception:%s',
                    $message,
                    $e->getMessage()
                )
            );
        }
        $this->reConnectDB();
    }

    /**
     * @param  string $syncType "rent" | "balance"
     * @param  Holding $holding
     * @param  string $externalPropertyId
     * @return Job
     */
    protected function createSyncJob($syncType, Holding $holding, $externalPropertyId)
    {
        switch ($syncType) {
            case self::SYNC_BALANCE:
                $logPrefix = '[SyncBalance]';
                $commandName = SyncContractBalanceCommand::NAME;
                break;
            case self::SYNC_RENT:
                $logPrefix = '[SyncRent]';
                $commandName = SyncContractRentCommand::NAME;
                break;
            default:
                throw new \RuntimeException('Unsupported synchronize type.');
        }

        $arguments = [
            '--holding-id=' . $holding->getId(),
            '--external-property-id=' . $externalPropertyId,
        ];

        $job = new Job($commandName, $arguments);
        $this->em->persist($job);
        $this->em->flush($job);

        $this->logger->info(
            sprintf(
                '%sCreated job#%d: to sync %s for holding: %s(#%d), external property: %s',
                $logPrefix,
                $job->getId(),
                $syncType,
                $holding->getName(),
                $holding->getId(),
                $externalPropertyId
            )
        );

        return $job;
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
                $this->createSyncJob(self::SYNC_BALANCE, $holding, $externalPropertyId);
            } catch (\Exception $e) {
                $this->handleException($e);
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
            $this->logger->debug(
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
                $this->handleException($e);
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
                $this->createSyncJob(self::SYNC_RENT, $holding, $externalPropertyId);
            } catch (\Exception $e) {
                $this->handleException($e);
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
