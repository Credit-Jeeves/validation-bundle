<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Helpers\PeriodicExecutor;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Model\MRI\Charge;
use RentJeeves\ExternalApiBundle\Model\MRI\Resident;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;

class ContractSynchronizer
{
    const COUNT_PROPERTIES_PER_SET = 20;

    /**
     * Run cleanup callback every EM_CLEANUP_PERIOD iterations
     */
    const EM_CLEANUP_PERIOD = 100;

    /**
     * @var PeriodicExecutor
     */
    protected $periodicExecutor;

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
     * @param EntityManager $em
     * @param ResidentDataManager $residentDataManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, ResidentDataManager $residentDataManager, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->residentDataManager = $residentDataManager;
        $this->periodicExecutor = new PeriodicExecutor(
            $this,
            'cleanupDoctrineCallback',
            self::EM_CLEANUP_PERIOD,
            $logger
        );
    }

    /**
     * We need to clean up some stuff in the EM periodically
     * to avoid having doctrine slow WAY down.
     */
    public function cleanupDoctrineCallback()
    {
        $this->logger->debug('Clearing Entity Manager');
        $this->em->clear();
    }

    /**
     * Execute synchronization balance
     */
    public function syncBalance()
    {
        try {
            $holdings = $this->getHoldingsForUpdateBalanceMRI();
            if (empty($holdings)) {
                $this->logger->info('MRI ResidentBalanceSynchronizer: No data to update');

                return;
            }

            foreach ($holdings as $holding) {
                $this->residentDataManager->setSettings($holding->getExternalSettings());
                $this->logger->info(
                    sprintf('MRI ResidentBalanceSynchronizer start work with holding %s', $holding->getId())
                );
                $this->updateBalancesForHolding($holding);
            }
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    '(MRI ResidentBalanceSynchronizer)Message: %s, File: %s, Line:%s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }
    }

    /**
     * @param Holding $holding
     * @throws \Exception
     */
    protected function updateBalancesForHolding(Holding $holding)
    {
        $propertyMappingRepository = $this->em->getRepository('RjDataBundle:PropertyMapping');
        $propertyMappingSets = ceil(
            $propertyMappingRepository->getCountUniqueByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );
        for ($offset = 1; $offset <= $propertyMappingSets; $offset++) {
            $propertyMappings = $propertyMappingRepository->findUniqueByHolding(
                $holding,
                $offset,
                self::COUNT_PROPERTIES_PER_SET
            );
            /** @var PropertyMapping $propertyMapping*/
            foreach ($propertyMappings as $propertyMapping) {
                try {
                    $this->updateBalancePerPropertyMapping($propertyMapping);
                } catch (\Exception $e) {
                    $this->logger->alert(
                        sprintf(
                            'MRIBalanceSync Exception updating balance: %s. StackTrace: %s',
                            $e->getMessage(),
                            $e->getTraceAsString()
                        )
                    );
                }
            }
        }
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @throws \Exception
     */
    protected function updateBalancePerPropertyMapping(PropertyMapping $propertyMapping)
    {
        $residentTransactions = $this->residentDataManager->getResidents(
            $propertyMapping->getExternalPropertyId()
        );
        $nextPageLink = $this->residentDataManager->getNextPageLink();
        while ($nextPageLink) {
            $this->logger->info(sprintf('MRIBalanceSync: get residents by next page link %s', $nextPageLink));
            $residentTransactionsByNextPageLink = $this->residentDataManager->getResidentsByNextPageLink(
                $nextPageLink
            );
            $nextPageLink = $this->residentDataManager->getNextPageLink();
            $residentTransactions = array_merge($residentTransactions, $residentTransactionsByNextPageLink);
        }

        if ($residentTransactions) {
            $this->logger->info(
                sprintf(
                    'MRI ResidentBalanceSynchronizer: Processing resident transactions for property %s of holding %s',
                    $propertyMapping->getExternalPropertyId(),
                    $propertyMapping->getHolding()->getName()
                )
            );
            $this->processResidentTransactionsForUpdateBalance($residentTransactions, $propertyMapping);

            return;
        }

        $noResidentsMessage = sprintf(
            'ERROR: Could not load resident transactions MRI for property %s of holding %s',
            $propertyMapping->getExternalPropertyId(),
            $propertyMapping->getHolding()->getName()
        );

        if ($propertyMapping->getProperty()->isSingle()) {
            // single-unit properties are likely to be vacant -- just log
            $this->logger->info($noResidentsMessage);
        } else {
            // multi-unit properties not are likely to be vacant -- send alert
            $this->logger->alert($noResidentsMessage);
        }
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding[]
     */
    protected function getHoldingsForUpdateBalanceMRI()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalanceMRI();
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding[]
     */
    protected function getHoldingsForUpdateRentMRI()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingRentMRI();
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param string $residentId
     * @param string $externalUnitId
     * @return null|Contract|ContractWaiting
     * @throws \Exception
     */
    protected function getContract(PropertyMapping $propertyMapping, $residentId, $externalUnitId)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $contracts = $contractRepo->findContractsByPropertyMappingResidentAndExternalUnitId(
            $propertyMapping,
            $residentId,
            $externalUnitId
        );

        if (count($contracts) > 1) {
            $message = sprintf(
                'MRI ResidentBalanceSynchronizer: Found more than one contract with property %s,
                 externalUnitId %s, residentId %s',
                $propertyMapping->getExternalPropertyId(),
                $externalUnitId,
                $residentId
            );
            $this->logger->alert($message);

            return null;
        }

        if (count($contracts) == 1) {
            /** @var Contract $contract */
            $contract = reset($contracts);

            return $contract;
        }

        $contractWaiting = null;
        try {
            $contractWaiting = $this->em->getRepository('RjDataBundle:ContractWaiting')
                ->findOneByPropertyMappingExternalUnitIdAndResidentId(
                    $propertyMapping,
                    $externalUnitId,
                    $residentId
                );
        } catch (\Doctrine\ORM\NonUniqueResultException $e) {
            $this->logger->alert(
                sprintf(
                    'MRIBalanceSync Exception: Duplicate mapping found cannot update balance: ' .
                    'property %s, externalUnitId %s, resident %s',
                    $propertyMapping->getExternalPropertyId(),
                    $externalUnitId,
                    $residentId
                )
            );
        }

        if ($contractWaiting) {
            $this->logger->debug(
                sprintf(
                    'MRI ResidentBalanceSynchronizer: Found contract waiting ID: %s',
                    $contractWaiting->getId()
                )
            );

            return $contractWaiting;
        }

        $this->logger->debug(
            sprintf(
                'MRI - could not find contract with property %s, externalUnitId %s, resident %s',
                $propertyMapping->getExternalPropertyId(),
                $externalUnitId,
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
    protected function processResidentTransactionsForUpdateBalance(
        array $residentTransactions,
        PropertyMapping $propertyMapping
    ) {
        /** @var $customer Value  */
        foreach ($residentTransactions as $customer) {
            try {
                $residentId = $customer->getResidentId();
                $externalUnitId = $customer->getExternalUnitId();
                $contract = $this->getContract($propertyMapping, $residentId, $externalUnitId);
                if (!$contract) {
                    continue;
                }

                $this->doUpdateBalance($customer, $contract);

                $this->em->flush();
                $this->periodicExecutor->increment();
            } catch (\Exception $e) {
                $this->logger->alert(
                    sprintf(
                        'MRIBalanceSync Exception updating balance for resident %s. Message: %s. StackTrace: %s',
                        $customer->getResidentId(),
                        $e->getMessage(),
                        $e->getTraceAsString()
                    )
                );
            }
        }
    }

    /**
     * @param Value $customer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdateBalance(Value $customer, $contract)
    {
        $contract->setPaymentAccepted($customer->getPaymentAccepted());
        $contract->setIntegratedBalance($customer->getLeaseBalance());
        $this->logger->info(
            sprintf(
                'MRI: payment accepted to %s, balance %s.
                For ContractID: %s',
                $contract->getPaymentAccepted(),
                $contract->getIntegratedBalance(),
                $contract->getId()
            )
        );
    }

    public function syncRecurringCharge()
    {
        $holdings = $this->getHoldingsForUpdateRentMRI();
        if (empty($holdings)) {
            $this->logger->info('MRI sync Recurring Charge: No data to update.');

            return;
        }

        foreach ($holdings as $holding) {
            $this->residentDataManager->setSettings($holding->getExternalSettings());
            $this->logger->info(
                sprintf('MRI ResidentBalanceSynchronizer start work with holding %s', $holding->getId())
            );
            $this->updateRentForHolding($holding);
        }
    }

    /**
     * @param Holding $holding
     */
    protected function updateRentForHolding(Holding $holding)
    {
        $propertyMappingRepository = $this->em->getRepository('RjDataBundle:PropertyMapping');
        $propertyMappingSets = ceil(
            $propertyMappingRepository->getCountUniqueByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );
        for ($offset = 1; $offset <= $propertyMappingSets; $offset++) {
            $propertyMappings = $propertyMappingRepository->findUniqueByHolding(
                $holding,
                $offset,
                self::COUNT_PROPERTIES_PER_SET
            );
            /** @var PropertyMapping $propertyMapping*/
            foreach ($propertyMappings as $propertyMapping) {
                try {
                    $this->updateRentPerPropertyMapping($propertyMapping, $holding);
                } catch (\Exception $e) {
                    $this->logger->alert(
                        sprintf(
                            'MRIRentSync Exception: %s. When update rent for MRI.',
                            $e->getMessage()
                        )
                    );
                }
            }

        }

    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param Holding $holding
     */
    protected function updateRentPerPropertyMapping(PropertyMapping $propertyMapping, Holding $holding)
    {
        $residentTransactions = $this->residentDataManager->getResidentsRentRoll(
            $propertyMapping->getExternalPropertyId()
        );

        while ($nextPageLink = $this->residentDataManager->getNextPageLink()) {
            $this->logger->info(sprintf('MRIRentSync: get residents RentRoll by next page link %s', $nextPageLink));
            $residentTransactionsByNextPageLink = $this->residentDataManager->getResidentsRentRollByNextPageLink(
                $nextPageLink
            );
            $residentTransactions = array_merge($residentTransactions, $residentTransactionsByNextPageLink);
        }

        if ($residentTransactions) {
            $this->logger->info(
                sprintf(
                    'MRI ResidentBalanceSynchronizer: Processing resident RentRoll transactions for property %s of ' .
                    'holding %s',
                    $propertyMapping->getExternalPropertyId(),
                    $holding->getName()
                )
            );
            $this->processResidentTransactionsForUpdateRent($residentTransactions, $propertyMapping);

            return;
        }

        $noResidentsMessage = sprintf(
            'ERROR: Could not load resident RentRoll transactions MRI for property %s of holding %s',
            $propertyMapping->getExternalPropertyId(),
            $holding->getName()
        );

        if ($propertyMapping->getProperty()->isSingle()) {
            // single-unit properties are likely to be vacant -- just log
            $this->logger->info($noResidentsMessage);
        } else {
            // multi-unit properties not are likely to be vacant -- send alert
            $this->logger->alert($noResidentsMessage);
        }
    }

    /**
     * @param array $residentTransactions
     * @param PropertyMapping $propertyMapping
     */
    protected function processResidentTransactionsForUpdateRent(
        array $residentTransactions,
        PropertyMapping $propertyMapping
    ) {
        /** @var Value $customer */
        foreach ($residentTransactions as $customer) {
            /** @var Resident $resident */
            foreach ($customer->getResidents()->getResidentArray() as $resident) {
                try {
                    $contract = $this->getContract(
                        $propertyMapping,
                        $resident->getResidentId(),
                        $customer->getExternalUnitId()
                    );

                    if (!$contract) {
                        continue;
                    }

                    $this->doUpdateRent($customer, $contract);
                } catch (\Exception $e) {
                    $this->logger->alert(
                        sprintf(
                            'MRIBalanceSync Exception updating rent for resident %s. Message: %s. StackTrace: %s',
                            $resident->getResidentId(),
                            $e->getMessage(),
                            $e->getTraceAsString()
                        )
                    );
                }
            }
        }
    }

    /**
     * @param Value $customer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdateRent(Value $customer, $contract)
    {
        $chargeCodes = $contract->getGroup()->getHolding()->getRecurringCodesArray();
        $currentCharges = $customer->getCurrentCharges();
        $charges = $currentCharges->getCharges();
        $amount = 0;
        /** @var Charge $charge */
        foreach ($charges as $charge) {
            if (strtolower($charge->getFrequency()) !== 'm') {
                $this->logger->info(sprintf('Frequency not equals "m" it "%s"', $charge->getFrequency()));
                continue;
            }

            $chargeCode = $charge->getChargeCode();
            if (!in_array($chargeCode, $chargeCodes) && !empty($chargeCodes)) {
                $this->logger->info(
                    sprintf(
                        'Charge code(%s) not in list (%s)',
                        $chargeCode,
                        $contract->getGroup()->getHolding()->getRecurringCodes()
                    )
                );
                continue;
            }

            $effectiveDate = $charge->getDateTimeEffectiveDate();
            $endDate = $charge->getDateTimeEndDate();

            if (!$this->checkDateFallsBetweenDates($effectiveDate, $endDate)) {
                continue;
            }

            $amount += $charge->getAmount();
        }

        if ($amount === 0) {
            $this->logger->info('Amount is 0');

            return;
        }

        $contract->setRent($amount);
        $this->em->flush();
        $this->periodicExecutor->increment();
        $this->logger->info(
            sprintf(
                'MRI: rent set %s.
                For ContractID: %s',
                $contract->getRent(),
                $contract->getId()
            )
        );
    }

    /**
     * @TODO the same in Yardi contract sync, merge this method when moving to abstract class the same code
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
