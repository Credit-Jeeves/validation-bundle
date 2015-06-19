<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use Symfony\Component\Console\Output\OutputInterface;

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
    public function run()
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
            /** @var Property $property*/
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
        if ($residentId === '510f76fc-d9d1-4fb7-bb95-ba2d2adc717f') {
            $var = 1;
        }
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
        //file_put_contents('/var/www/Credit-Jeeves-SF2/dump.txt', print_r($residentTransactions, true));
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
        $contract->setExternalLeaseId($baseCustomer->getCustomerId());
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
}
