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
    const COUNT_PROPERTIES_PER_SET = 20;

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
        try {
            $holdings = $this->getHoldingsForUpdatingBalance();
            if (empty($holdings)) {
                $this->logMessage('[SyncBalance]No data to update');

                return;
            }

            foreach ($holdings as $holding) {
                $this->setExternalSettings($holding);
                $holdingId = $holding->getId();
                $this->em->clear($holding);
                $this->logMessage(
                    sprintf(
                        '[SyncBalance]Processing holding "%s" #%d',
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                $this->updateBalancesForHolding($holdingId);
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

    /**
     * Execute synchronization rent of contracts
     */
    public function syncRent()
    {
        try {
            $holdings = $this->getHoldingsForUpdatingRent();
            if (empty($holdings)) {
                $this->logMessage('[SyncRent]No data to update.');

                return;
            }

            foreach ($holdings as $holding) {
                $this->setExternalSettings($holding);
                $holdingId = $holding->getId();
                $this->em->clear($holding);
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Processing holding "%s" #%d',
                        $holding->getName(),
                        $holding->getId()
                    )
                );
                $this->updateContractsRentForHolding($holdingId);
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
     */
    abstract protected function setExternalSettings(Holding $holding);

    /**
     * @return Holding[]
     */
    abstract protected function getHoldingsForUpdatingBalance();

    /**
     * @param int $holdingId
     * @throws \RuntimeException
     */
    protected function updateBalancesForHolding($holdingId)
    {
        $repo = $this->getPropertyRepository();
        $propertySets = ceil($repo->countContractPropertiesByHolding($holdingId) / static::COUNT_PROPERTIES_PER_SET);
        $this->logMessage(sprintf('[SyncBalance]Found %d pages of property for processing.', $propertySets));

        for ($offset = 1; $offset <= $propertySets; $offset++) {
            $this->logMessage(sprintf('[SyncBalance]Start processing page %d of property.', $offset));
            $properties = $repo->findContractPropertiesByHolding($holdingId, $offset, static::COUNT_PROPERTIES_PER_SET);
            foreach ($properties as $property) {
                try {
                    $propertyMapping = $property->getPropertyMappingByHoldingId($holdingId);
                    if (empty($propertyMapping)) {
                        throw new \RuntimeException(
                            sprintf(
                                'PropertyID "%d" doesn\'t have external property ID',
                                $property->getId()
                            )
                        );
                    }
                    $this->logMessage(
                        sprintf(
                            '[SyncBalance]Try processing property #%d with external id "%s"',
                            $property->getId(),
                            $propertyMapping->getExternalPropertyId()
                        )
                    );

                    $residentTransactions = $this->residentDataManager->getResidentTransactions(
                        $propertyMapping->getExternalPropertyId()
                    );
                    if (empty($residentTransactions) && !$propertyMapping->getProperty()->isSingle()) {
                        // multi-unit properties not are likely to be vacant -- send alert
                        throw new \LogicException(
                            sprintf(
                                'Could not load resident transactions for property %s of holding %s',
                                $propertyMapping->getExternalPropertyId(),
                                $propertyMapping->getHolding()->getName()
                            )
                        );
                    }
                    $this->logMessage(
                        sprintf(
                            '[SyncBalance]Find %d resident transactions for processing by property %s of holding %s',
                            count($residentTransactions),
                            $property->getId(),
                            $propertyMapping->getExternalPropertyId()
                        )
                    );

                    foreach ($residentTransactions as $resident) {
                        $this->updateContractBalanceForResidentTransaction($resident, $propertyMapping);
                        $this->em->flush();
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
            /** There we clear entity manager b/c we have a lot of object for UnitOfWork processing */
            $this->em->clear();
        }
    }

    /**
     * @param mixed $resident
     * @param PropertyMapping $propertyMapping
     */
    abstract protected function updateContractBalanceForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    );

    /**
     * @return Holding[]
     */
    abstract protected function getHoldingsForUpdatingRent();

    /**
     * @param int $holdingId
     */
    protected function updateContractsRentForHolding($holdingId)
    {
        $propertyMappingRepository = $this->getPropertyMappingRepository();
        $countPropertyMappingSets = ceil(
            $propertyMappingRepository->getCountUniqueByHolding($holdingId) / self::COUNT_PROPERTIES_PER_SET
        );
        $this->logMessage(
            sprintf(
                '[SyncRent]Found %d pages of property mapping for processing.',
                $countPropertyMappingSets
            )
        );

        for ($offset = 1; $offset <= $countPropertyMappingSets; $offset++) {
            $this->logMessage(sprintf('[SyncRent]Start processing page %d of property mapping.', $offset));
            $propertyMappings = $propertyMappingRepository->findUniqueByHolding(
                $holdingId,
                $offset,
                static::COUNT_PROPERTIES_PER_SET
            );

            foreach ($propertyMappings as $propertyMapping) {
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
                    foreach ($residentTransactions as $resident) {
                        $this->updateContractRentForResidentTransaction($resident, $propertyMapping);
                        $this->em->flush();
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
    }

    /**
     * @param mixed $resident
     * @param PropertyMapping $propertyMapping
     */
    abstract protected function updateContractRentForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    );

    /**
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
}
