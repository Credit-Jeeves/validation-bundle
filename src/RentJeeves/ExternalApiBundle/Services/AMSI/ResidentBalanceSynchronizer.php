<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use Symfony\Component\Console\Output\OutputInterface;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;

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
    }

    /**
     * Execute synchronization balance
     */
    public function run()
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
                            'AMSI ResidentBalanceSynchronizer: Processing resident transactions for property %s of
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
                        'ERROR: Could not load resident transactions for property %s of holding %s',
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
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalanceAMSI();
    }

    /**
     * @param Holding $holding
     * @param Property $property
     * @param Lease $lease
     * @param Occupant $occupant
     * @return null|Contract|ContractWaiting
     * @throws \Exception
     */
    protected function getContract(Holding $holding, Property $property, Lease $lease, Occupant $occupant)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $residentId = $occupant->getOccuSeqNo();

        $this->logMessage(
            sprintf(
                'Getting contract for holding %s, property %s, lease %s, residentId %s',
                $holding->getId(),
                $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
                $lease->getExternalUnitId(),
                $residentId
            )
        );

        $contracts = $contractRepo->findContractsByHoldingPropertyResidentAndExternalUnitId(
            $holding,
            $property,
            $residentId,
            $lease->getExternalUnitId()
        );

        if (count($contracts) > 1) {
            $this->logMessage(
                sprintf(
                    'Found more than one contract with property %s, externalUnitId %s, residentId %s',
                    $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
                    $lease->getExternalUnitId(),
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
            ->findOneByHoldingPropertyExternalUnitIdResident(
                $holding,
                $property,
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
                $property->getPropertyMappingByHolding($holding)->getExternalPropertyId(),
                $lease->getExternalUnitId(),
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
        /** @var Lease $resident */
        foreach ($residentTransactions as $resident) {
            $occupants = $resident->getOccupants();
            foreach ($occupants as $occupant) {
                $contract = $this->getContract($holding, $property, $resident, $occupant);
                if (!$contract) {
                    continue;
                }

                $this->doUpdate($resident, $occupant, $contract);
            }
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
        if (strtolower($disallow) === 'n') {
            $disallow = PaymentAccepted::ANY;
        } else {
            $disallow = PaymentAccepted::DO_NOT_ACCEPT;
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
    protected function logMessage($message)
    {
        $this->logger->debug($message);
        if ($this->outputLogger) {
            $this->outputLogger->writeln($message);
        }
    }
}
