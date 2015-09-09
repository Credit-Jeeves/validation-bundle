<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\PropertyMappingRepository;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use RentJeeves\ExternalApiBundle\Model\AMSI\RecurringCharge;
use Symfony\Component\Console\Output\OutputInterface;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;

class ContractSynchronizer
{
    const COUNT_PROPERTIES_PER_SET = 20;
    const COUNT_RESODENTS_FOR_FLUSH = 20;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

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
     * @param ExceptionCatcher $exceptionCatcher
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $em,
        ResidentDataManager $residentDataManager,
        ExceptionCatcher $exceptionCatcher,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->residentDataManager = $residentDataManager;
        $this->exceptionCatcher = $exceptionCatcher;
    }

    /**
     * Execute synchronization balance
     */
    public function syncBalance()
    {
        try {
            $holdings = $this->getHoldings();
            if (empty($holdings)) {
                return $this->logMessage('AMSI ResidentBalanceSynchronizer: No data to update');
            }

            foreach ($holdings as $holding) {
                $this->residentDataManager->setSettings($holding->getExternalSettings());
                $this->logMessage(
                    sprintf('AMSI ResidentBalanceSynchronizer start work with holding %s', $holding->getId())
                );
                $this->updateBalancesForHolding($holding);
            }
        } catch (\Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logger->alert(
                sprintf(
                    '(AMSI ResidentBalanceSynchronizer)Message: %s, File: %s, Line:%s',
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
        /** @var PropertyMappingRepository $propertyMappingRepository */
        $propertyMappingRepository = $this->em->getRepository('RjDataBundle:PropertyMapping');
        $propertyMappingSets = ceil(
            $propertyMappingRepository->getCountUniqueByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );
        $this->logMessage(sprintf('Found %d pages of property mappings', $propertyMappingSets));
        for ($offset = 1; $offset <= $propertyMappingSets; $offset++) {
            $this->logMessage(sprintf('Open %d page of property mappings', $offset));
            $propertyMappings = $propertyMappingRepository->findUniqueByHolding(
                $holding,
                $offset,
                self::COUNT_PROPERTIES_PER_SET
            );
            foreach ($propertyMappings as $propertyMapping) {
                $this->updateBalancesForPropertyMapping($propertyMapping);
            }
            $this->em->flush();
            $this->em->clear();
        }
    }

    /**
     * @param PropertyMapping $propertyMapping
     */
    protected function updateBalancesForPropertyMapping(PropertyMapping $propertyMapping)
    {
        $this->logMessage(
            sprintf(
                'AMSI ResidentBalanceSynchronizer: start work with propertyMapping \'%s\'',
                $propertyMapping->getExternalPropertyId()
            )
        );

        try {
            $residentTransactions = $this->residentDataManager->getResidents(
                $propertyMapping->getExternalPropertyId()
            );

            if (false == $residentTransactions) {
                $this->logMessage(
                    sprintf(
                        'AMSI ResidentBalanceSynchronizer: Not found transactions for property %s of
                         holding %s',
                        $propertyMapping->getExternalPropertyId(),
                        $propertyMapping->getHolding()->getName()
                    )
                );

                return;
            }

            $this->logMessage(
                sprintf(
                    'AMSI ResidentBalanceSynchronizer: Processing resident transactions for property %s of
                         holding %s',
                    $propertyMapping->getExternalPropertyId(),
                    $propertyMapping->getHolding()->getName()
                )
            );

            $this->processResidentTransactions($residentTransactions, $propertyMapping);
        } catch (\Exception $e) {
            $this->logMessage(
                sprintf(
                    'AMSI ResidentBalanceSynchronizer: Error: %s',
                    $e->getMessage()
                ),
                500
            );
        }
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding[]
     */
    protected function getHoldings()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalanceAMSI();
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param Lease $lease
     * @param Occupant $occupant
     * @return null|Contract|ContractWaiting
     * @throws \Exception
     */
    protected function getContract(PropertyMapping $propertyMapping, Lease $lease, Occupant $occupant)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $residentId = $occupant->getOccuSeqNo();

        $this->logMessage(
            sprintf(
                'Getting contract for holding %s, propertyMapping %s, lease %s, residentId %s',
                $propertyMapping->getHolding()->getId(),
                $propertyMapping->getExternalPropertyId(),
                $lease->getExternalUnitId(),
                $residentId
            )
        );

        $contracts = $contractRepo->findContractsByPropertyMappingResidentAndExternalUnitId(
            $propertyMapping,
            $residentId,
            $lease->getExternalUnitId()
        );

        if (count($contracts) > 1) {
            $this->logMessage(
                sprintf(
                    'Found more than one contract with property %s, externalUnitId %s, residentId %s',
                    $propertyMapping->getExternalPropertyId(),
                    $lease->getExternalUnitId(),
                    $residentId
                ),
                550
            );

            return null;
        }

        if (count($contracts) == 1) {
            /** @var Contract $contract */
            $contract = reset($contracts);

            return $contract;
        }

        $contractWaiting = $this->em->getRepository('RjDataBundle:ContractWaiting')
            ->findOneByPropertyMappingExternalUnitIdAndResidentId(
                $propertyMapping,
                $lease->getExternalUnitId(),
                $residentId
            );
        if ($contractWaiting) {
            $this->logMessage(sprintf('Return contract waiting ID: %s', $contractWaiting->getId()));

            return $contractWaiting;
        }

        $this->logMessage(
            sprintf(
                'Could not find contract with property %s, unit %s, resident %s',
                $propertyMapping->getExternalPropertyId(),
                $lease->getExternalUnitId(),
                $residentId
            )
        );

        return null;
    }

    /**
     * @param array $residentTransactions
     * @param PropertyMapping $propertyMapping
     * @throws \Exception
     */
    protected function processResidentTransactions(
        array $residentTransactions,
        PropertyMapping $propertyMapping
    ) {
        $holding = $propertyMapping->getHolding();
        /** @var Lease $lease */
        foreach ($residentTransactions as $lease) {
            $counter = 0;
            foreach ($lease->getOccupants() as $occupant) {
                if (false != $contract = $this->getContract($holding, $propertyMapping, $lease, $occupant)) {
                    $this->doUpdate($lease, $occupant, $contract);
                    $counter++;

                    if ($counter === self::COUNT_RESODENTS_FOR_FLUSH) {
                        $this->em->flush();
                        $counter = 0;
                    }
                }
            }

            $this->em->flush();
        }
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdate(Lease $lease, Occupant $occupant, $contract)
    {
        $residentId = $occupant->getOccuSeqNo();
        $disallow = $lease->getBlockPaymentAccess();
        $externalLeaseId = $lease->getResiId();
        $balance = $lease->getEndBalance();
        if (strtolower($disallow) === 'y') {
            $disallow = PaymentAccepted::DO_NOT_ACCEPT;
        } else {
            $disallow = PaymentAccepted::ANY;
        }
        $contract->setPaymentAccepted($disallow);
        $currentExternalLeaseId = $contract->getExternalLeaseId();
        if (empty($currentExternalLeaseId)) {
            $contract->setExternalLeaseId($lease->getExternalUnitId());
        }
        $contract->setIntegratedBalance($balance);
        $this->logMessage(
            sprintf(
                'Set value to update: payment accepted to %s, residentId to %s, externalUnitId to %s, leaseId to %s.
                For ContractID: %s',
                $disallow,
                $residentId,
                $lease->getExternalUnitId(),
                $externalLeaseId,
                $contract->getId()
            )
        );
    }

    /**
     * @param string $message
     */
    protected function logMessage($message, $level = 100)
    {
        $this->logger->log($level, $message);
        if ($this->outputLogger) {
            $this->outputLogger->writeln($message);
        }
    }

    public function syncRecurringCharge()
    {
        $holdings = $this->getHoldingRepository()->findHoldingsForAMSISyncRecurringCharges();
        if (empty($holdings)) {
            $this->logMessage('AMSI sync Recurring Charge: No data to update.');
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
        $this->logMessage(sprintf('AMSI sync Recurring Charge: start work with holding %d', $holding->getId()));

        $this->residentDataManager->setSettings($holding->getExternalSettings());
        $countPropertyMappingSets = ceil(
            $this->getPropertyMappingRepository()->getCountUniqueByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );

        $this->logMessage(sprintf('Found %d pages of property mappings', $countPropertyMappingSets));

        for ($offset = 1; $offset <= $countPropertyMappingSets; $offset++) {
            $this->logMessage(sprintf('Open %d page of property mappings', $offset));

            $propertyMappings = $this->getPropertyMappingRepository()->findUniqueByHolding(
                $holding,
                $offset,
                self::COUNT_PROPERTIES_PER_SET
            );

            /** @var PropertyMapping $propertyMapping */
            foreach ($propertyMappings as $propertyMapping) {
                $this->updateContractsRentForPropertyMapping($propertyMapping);
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
                'AMSI sync Recurring Charge: start work with propertyMapping \'%s\'',
                $propertyMapping->getExternalPropertyId()
            )
        );

        try {
            $residentTransactions = $this->residentDataManager->getResidentsWithRecurringCharges(
                $propertyMapping->getExternalPropertyId()
            );
        } catch (\Exception $e) {
            $this->logMessage(
                sprintf(
                    'AMSI sync Recurring Charge: \'%s\'',
                    $e->getMessage()
                ),
                500
            );

            return;
        }

        if (false == $residentTransactions) {
            $this->logMessage(
                sprintf(
                    'AMSI sync Recurring Charge: ERROR:
                    Could not load resident transactions for Property %s of Holding#%d',
                    $propertyMapping->getExternalPropertyId(),
                    $propertyMapping->getHolding()->getId()
                ),
                500
            );

            return;
        }

        foreach ($residentTransactions as $residentTransaction) {
            $this->updateContractsRentForResidentTransaction($propertyMapping, $residentTransaction);
        }
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param Lease $lease
     */
    protected function updateContractsRentForResidentTransaction(PropertyMapping $propertyMapping, Lease $lease)
    {
        $this->logMessage('AMSI sync Recurring Charge: Searching for contracts.');

        $contractIds = [];
        foreach ($lease->getOccupants() as $occupant) {
            if (null !== $contract = $this->getContract($propertyMapping, $lease, $occupant)) {
                $contractIds[] = $contract->getId();
            }
        }

        if (count($contractIds) === 0) {
            $this->logMessage('AMSI sync Recurring Charge: Contracts not found.');

            return;
        }

        if (false == $recurringCodes = explode(',', $propertyMapping->getHolding()->getRecurringCodes())) {
            $this->logMessage(
                sprintf(
                    'AMSI sync Recurring Charge: ERROR: Holding#%d does not have RecurringCodes',
                    $propertyMapping->getHolding()->getId()
                ),
                500
            );

            return;
        }

        $sumRecurringCharges = $this->getSumRecurringCharges($lease, $recurringCodes);

        if ($sumRecurringCharges <= 0) {
            $this->logMessage(
                sprintf(
                    'AMSI sync Recurring Charge: ERROR: sum of RecurringCharges for contracts(%s) = %d',
                    implode(', ', $contractIds),
                    $sumRecurringCharges
                ),
                500
            );

            return;
        }

        $this->updateRentForContractIds($sumRecurringCharges, $contractIds);
        $this->logMessage(
            sprintf(
                'AMSI sync Recurring Charge: Rent for contracts (%s) updated',
                implode(', ', $contractIds)
            )
        );
    }

    /**
     * @param $rent
     * @param array $contractIds
     *
     * @return int
     */
    protected function updateRentForContractIds($rent, array $contractIds)
    {
        return $this->em->createQueryBuilder()
            ->update('RjDataBundle:Contract', 'c')
            ->set('c.rent', $rent)
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $contractIds)
            ->getQuery()
            ->execute();
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
            if (in_array($recurringCharge->getIncCode(), $recurringCodes) && $recurringCharge->getFreqCode() === 'M') {
                $sumRecurringCharges += $recurringCharge->getAmount();
            }
        }

        return $sumRecurringCharges;
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
}
