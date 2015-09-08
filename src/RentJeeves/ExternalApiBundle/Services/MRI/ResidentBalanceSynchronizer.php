<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;

class ResidentBalanceSynchronizer
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
    public function run()
    {
        try {
            $holdings = $this->getHoldings();
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
            $this->processResidentTransactions($residentTransactions, $holding, $property);

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
    protected function getHoldings()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalanceMRI();
    }

    /**
     * @param Holding $holding
     * @param Property $property
     * @param Value $customer
     * @return null|Contract|ContractWaiting
     * @throws \Exception
     */
    protected function getContract(Holding $holding, Property $property, Value $customer)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $residentId = $customer->getResidentId();
        $externalUnitId = $customer->getExternalUnitId();
        $contracts = $contractRepo->findContractsByHoldingPropertyMappingResidentAndExternalUnitId(
            $holding,
            $property->getPropertyMappingByHolding($holding),
            $residentId,
            $externalUnitId
        );

        if (count($contracts) > 1) {
            $message = sprintf(
                'MRI ResidentBalanceSynchronizer: Found more than one contract with property %s,
                 externalUnitId %s, residentId %s',
                $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
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
            ->findOneByHoldingPropertyMappingExternalUnitIdResident(
                $holding,
                $property->getPropertyMappingByHolding($holding),
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
                $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
                $externalUnitId,
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
        /** @var $customer Value  */
        foreach ($residentTransactions as $customer) {
            $contract = $this->getContract($holding, $property, $customer);
            if (!$contract) {
                continue;
            }

            $this->doUpdate($customer, $contract);
        }
    }

    /**
     * @param Value $customer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdate(Value $customer, $contract)
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
