<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Exception;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyRepository;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @DI\Service("amsi.resident_balance_sync")
 */
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
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "residentDataManager" = @DI\Inject("amsi.resident_data"),
     *     "logger" = @DI\Inject("logger")
     * })
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
                return $this->logMessage('No data to update');
            }

            foreach ($holdings as $holding) {
                $this->residentDataManager->setSettings($holding->getExternalSettings());
                $this->updateBalancesForHolding($holding);
            }
        } catch (Exception $e) {
            $this->logger->alert(
                sprintf('Message: %s, File: %s, Line:%s', $e->getMessage(), $e->getLine(), $e->getFile())
            );

            return $this->logMessage($e->getMessage());
        }
    }

    /**
     * @param OutputInterface $outputLogger
     * @return $this
     */
    public function usingOutput(OutputInterface $outputLogger)
    {
        $this->outputLogger = $outputLogger;

        return $this;
    }

    /**
     * @param Holding $holding
     * @throws Exception
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
            /** @var $property Property */
            foreach ($properties as $property) {
                $propertyMapping = $property->getPropertyMappingByHolding($holding);

                if (empty($propertyMapping)) {
                    throw new \Exception(
                        sprintf(
                            'PropertyID \'%s\', don\'t have external ID',
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
                            'Process: resident transactions for property %s of holding %s',
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
     * @throws Exception
     */
    protected function getContract(Holding $holding, Property $property, Lease $lease, Occupant $occupant)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $residentId = $occupant->getOccuSeqNo();
        $externalUnitId = sprintf(
            '%s|%s|%s',
            $lease->getPropertyId(),
            $lease->getBldgId(),
            $lease->getUnitId()
        );

        $contracts = $contractRepo->findContractByHoldingPropertyResidentAndExternalUnitId(
            $holding,
            $property,
            $residentId,
            $externalUnitId
        );

        if (count($contracts) > 1) {
            $propertyMapping = $property->getPropertyMappingByHolding($holding);
            if (empty($propertyMapping)) {
                throw new \Exception(
                    sprintf(
                        'PropertyID \'%s\', don\'t have external ID',
                        $property->getId()
                    )
                );
            }
            $this->logMessage(
                sprintf(
                    'Found more than one contract with property %s, externalUnitId %s, residentId %s',
                    $propertyMapping->getExternalPropertyId(),
                    $externalUnitId,
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
            ->findByHoldingPropertyExternaUnitIdResident($holding, $property, $externalUnitId, $residentId);
        if ($contractWaiting) {
            $this->logMessage(sprintf('Return contract waiting ID: %s', $contractWaiting->getId()));

            return $contractWaiting;
        }

        $propertyMapping = $property->getPropertyMappingByHolding($holding);

        if (empty($propertyMapping)) {
            throw new \Exception(
                sprintf(
                    'PropertyID "%s", don\'t have external ID',
                    $property->getId()
                )
            );
        }

        $this->logMessage(
            sprintf(
                'Could not find contract with property %s, unit %s, resident %s',
                $propertyMapping->getExternalPropertyId(),
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
     * @throws Exception
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
        $paymentAccepted = $lease->getBlockPaymentAccess();
        $externalLeaseId = $lease->getResiId();
        $balance = $lease->getEndBalance();
        if (strtolower($paymentAccepted) === 'n') {
            $paymentAccepted = PaymentAccepted::ANY;
        } else {
            $paymentAccepted = PaymentAccepted::DO_NOT_ACCEPT;
        }
        $externalUnitId = sprintf(
            '%s|%s|%s',
            $lease->getPropertyId(),
            $lease->getBldgId(),
            $lease->getUnitId()
        );
        $contract->setPaymentAccepted($paymentAccepted);
        $contract->setExternalLeaseId($externalLeaseId);
        $contract->setIntegratedBalance($balance);
        $this->logMessage(
            sprintf(
                'Set value to update: payment accepted to %s, residentId to %s, externalUnitId to %s, leaseId to %s.
                For ContractID: %s',
                $paymentAccepted,
                $residentId,
                $externalUnitId,
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
