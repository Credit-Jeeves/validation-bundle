<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
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
    }

    /**
     * Execute synchronization balance
     */
    public function syncBalance()
    {
        try {
            $holdings = $this->getHoldingsForUpdateBalanceMRI();
            if (empty($holdings)) {
                $this->logMessage('MRI ResidentBalanceSynchronizer: No data to update');

                return;
            }

            foreach ($holdings as $holding) {
                $this->residentDataManager->setSettings($holding->getExternalSettings());
                $this->logMessage(
                    sprintf('MRI ResidentBalanceSynchronizer start work with holding %s', $holding->getId())
                );
                $this->updateBalancesForHolding($holding);
            }
        } catch (\Exception $e) {
            $this->logMessage(
                sprintf(
                    '(MRI ResidentBalanceSynchronizer)Message: %s, File: %s, Line:%s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                true
            );
        }
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
            /** @var Property $property*/
            foreach ($properties as $property) {
                try {
                    $this->updateBalancePerProperty($property, $holding);
                } catch (\Exception $e) {
                    $this->logMessage(
                        sprintf(
                            'MRIBalanceSync Exception: %s. When Update balance for MRI.',
                            $e->getMessage()
                        ),
                        true
                    );
                }
            }
            $this->em->flush();
            $this->em->clear();
        }
    }

    /**
     * @param Property $property
     * @param Holding $holding
     * @throws \Exception
     */
    protected function updateBalancePerProperty(Property $property, Holding $holding)
    {
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
        $nextPageLink = $this->residentDataManager->getNextPageLink();
        while ($nextPageLink) {
            $this->logMessage(sprintf('MRIBalanceSync: get residents by next page link %s', $nextPageLink));
            $residentTransactionsByNextPageLink = $this->residentDataManager->getResidentsByNextPageLink(
                $nextPageLink
            );
            $nextPageLink = $this->residentDataManager->getNextPageLink();
            $residentTransactions = array_merge($residentTransactions, $residentTransactionsByNextPageLink);
        }

        if ($residentTransactions) {
            $this->logMessage(
                sprintf(
                    'MRI ResidentBalanceSynchronizer: Processing resident transactions for property %s of
                             holding %s',
                    $propertyMapping->getExternalPropertyId(),
                    $holding->getName()
                )
            );
            $this->processResidentTransactionsForUpdateBalance($residentTransactions, $propertyMapping);

            return;
        }

        $this->logMessage(
            sprintf(
                'ERROR: Could not load resident transactions MRI for property %s of holding %s',
                $propertyMapping->getExternalPropertyId(),
                $holding->getName()
            )
        );
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
            $this->logMessage($message, true);

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
                $externalUnitId,
                $residentId
            );

        if ($contractWaiting) {
            $this->logMessage(
                sprintf(
                    'MRI ResidentBalanceSynchronizer: Found contract waiting ID: %s',
                    $contractWaiting->getId()
                )
            );

            return $contractWaiting;
        }

        $this->logMessage(
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
            $residentId = $customer->getResidentId();
            $externalUnitId = $customer->getExternalUnitId();
            $contract = $this->getContract($propertyMapping, $residentId, $externalUnitId);
            if (!$contract) {
                continue;
            }

            $this->doUpdateBalance($customer, $contract);
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
        $this->em->persist($contract);
        $this->em->flush($contract);
        $this->logMessage(
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
            $this->logMessage('MRI sync Recurring Charge: No data to update.');

            return;
        }

        foreach ($holdings as $holding) {
            $this->residentDataManager->setSettings($holding->getExternalSettings());
            $this->logMessage(
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
        $propertyRepository = $this->em->getRepository('RjDataBundle:PropertyMapping');
        $propertyMappingSets = ceil(
            $propertyRepository->getCountUniqueByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );
        for ($offset = 1; $offset <= $propertyMappingSets; $offset++) {
            $propertyMappings = $propertyRepository->findUniqueByHolding(
                $holding,
                $offset,
                self::COUNT_PROPERTIES_PER_SET
            );
            /** @var Property $propertyMapping*/
            foreach ($propertyMappings as $propertyMapping) {
                try {
                    $this->updateRentPerPropertyMapping($propertyMapping, $holding);
                } catch (\Exception $e) {
                    $this->logMessage(
                        sprintf(
                            'MRIRentSync Exception: %s. When Update balance for MRI.',
                            $e->getMessage()
                        ),
                        true
                    );
                }
            }
            $this->em->flush();
            $this->em->clear();
        }

    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param Holding $holding
     */
    public function updateRentPerPropertyMapping(PropertyMapping $propertyMapping, Holding $holding)
    {
        $residentTransactions = $this->residentDataManager->getResidentsRentRoll(
            $propertyMapping->getExternalPropertyId()
        );
        $nextPageLink = $this->residentDataManager->getNextPageLink();
        while ($nextPageLink) {
            $this->logMessage(sprintf('MRIRentSync: get residents RentRoll by next page link %s', $nextPageLink));
            $residentTransactionsByNextPageLink = $this->residentDataManager->getResidentsRentRollByNextPageLink(
                $nextPageLink
            );
            $nextPageLink = $this->residentDataManager->getNextPageLink();
            $residentTransactions = array_merge($residentTransactions, $residentTransactionsByNextPageLink);
        }

        if ($residentTransactions) {
            $this->logMessage(
                sprintf(
                    'MRI ResidentBalanceSynchronizer: Processing resident RentRoll transactions for property %s of
                             holding %s',
                    $propertyMapping->getExternalPropertyId(),
                    $holding->getName()
                )
            );
            $this->processResidentTransactionsForUpdateRent($residentTransactions, $propertyMapping);

            return;
        }

        $this->logMessage(
            sprintf(
                'ERROR: Could not load resident RentRoll transactions MRI for property %s of holding %s',
                $propertyMapping->getExternalPropertyId(),
                $holding->getName()
            )
        );
    }
    /**
     * @param array $residentTransactions
     * @param PropertyMapping $propertyMapping
     * @throws \Exception
     */
    protected function processResidentTransactionsForUpdateRent(
        array $residentTransactions,
        PropertyMapping $propertyMapping
    ) {
        /** @var $customer Value  */
        foreach ($residentTransactions as $customer) {
            /** @var Resident $resident */
            foreach ($customer->getResidents()->getResidentArray() as $resident) {
                $contract = $this->getContract(
                    $propertyMapping,
                    $resident->getResidentId(),
                    $customer->getExternalUnitId()
                );

                if (!$contract) {
                    continue;
                }

                $this->doUpdateRent($customer, $contract);
            }
        }
    }

    /**
     * @param Value $customer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdateRent(Value $customer, $contract)
    {
        if ($contract instanceof Contract) {
            $chargeCodes = $contract->getHolding()->getRecurringCodesArray();
        } else {
            $chargeCodes = $contract->getGroup()->getHolding()->getRecurringCodesArray();
        }
        $currentCharges = $customer->getCurrentCharges();
        $charges = $currentCharges->getCharges();
        $amount = 0;
        /** @var Charge $charge */
        foreach ($charges as $charge) {
            if (strtolower($charge->getFrequency()) !== 'm') {
                $this->logMessage('Frequency not equals "m"');
                continue;
            }

            $chargeCode = $charge->getChargeCode();
            if (!in_array($chargeCode, $chargeCodes)) {
                $this->logMessage('Charge code not in list');
                continue;
            }

            $amount += $charge->getAmount();
        }

        if ($amount === 0) {
            return;
        }

        $contract->setRent($amount);
        $this->em->flush($contract);
        $this->logMessage(
            sprintf(
                'MRI: rent set %s.
                For ContractID: %s',
                $contract->getRent(),
                $contract->getId()
            )
        );
    }

    /**
     * @param string $message
     * @param boolean $alert
     */
    protected function logMessage($message, $alert = false)
    {
        if ($alert) {
            $this->logger->alert($message);
        }
        $this->logger->info($message);
    }
}
