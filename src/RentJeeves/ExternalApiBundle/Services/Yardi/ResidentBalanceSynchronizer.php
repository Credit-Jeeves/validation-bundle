<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @DI\Service("yardi.resident_balance_sync")
 */
class ResidentBalanceSynchronizer
{
    const COUNT_PROPERTIES_PER_SET = 20;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SoapClientFactory
     */
    protected $clientFactory;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var OutputInterface
     */
    protected $outputLogger;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     *     "exceptionCatcher" = @DI\Inject("fp_badaboom.exception_catcher"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(
        EntityManager $em,
        SoapClientFactory $clientFactory,
        ExceptionCatcher $exceptionCatcher,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->logger = $logger;
    }

    public function run()
    {
        try {
            $holdings = $this->getHoldings();
            if (empty($holdings)) {
                return $this->logMessage('No data to update');
            }

            foreach ($holdings as $holding) {
                $this->updateBalancesForHolding($holding);
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);

            return $this->logMessage($e->getMessage());
        }
    }

    public function usingOutput(OutputInterface $logger)
    {
        $this->outputLogger = $logger;

        return $this;
    }

    protected function updateBalancesForHolding(Holding $holding)
    {
        $repo = $this->em->getRepository('RjDataBundle:Property');

        /** @var $residentClient ResidentTransactionsClient */
        $residentClient = $this->clientFactory->getClient(
            $holding->getYardiSettings(),
            SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS
        );
        $propertySets = ceil($repo->countContractPropertiesByHolding($holding) / self::COUNT_PROPERTIES_PER_SET);
        for ($offset = 1; $offset <= $propertySets; $offset++) {
            $properties = $repo->findContractPropertiesByHolding($holding, $offset, self::COUNT_PROPERTIES_PER_SET);
            /** @var $property Property */
            foreach ($properties as $property) {
                $propertyMapping = $property->getPropertyMappingByHolding($holding);
                if (empty($propertyMapping)) {
                    throw new \Exception(
                        sprintf(
                            "PropertyID '%s', don't have external ID",
                            $property->getId()
                        )
                    );
                }
                $residentTransactions = $residentClient->getResidentTransactions(
                    $propertyMapping->getExternalPropertyId()
                );
                if ($residentTransactions) {
                    $this->processResidentTransactions($residentTransactions, $holding, $property);
                } else {
                    $this->logMessage(sprintf(
                        'ERROR: Could not load resident transactions for property %s of holding %s: %s',
                        $propertyMapping->getExternalPropertyId(),
                        $holding->getName(),
                        $residentClient->getErrorMessage()
                    ));
                }
            }
            $this->em->flush();
            $this->em->clear();
        }
    }

    protected function getHoldings()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalanceYardi();
    }

    /**
     * @param Holding $holding
     * @param Property $property
     * @param ResidentTransactionPropertyCustomer $resident
     * @return Contract
     * @throws Exception
     */
    protected function getContract(Holding $holding, Property $property, ResidentTransactionPropertyCustomer $resident)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $residentId = $resident->getCustomerId();
        $unitName = $resident->getUnit()->getUnitId();

        $this->logMessage(
            sprintf(
                'Start Search contract by residentId:%s, property:%s, holding:%s, unitName:%s',
                $residentId,
                $property->getId(),
                $holding->getId(),
                $unitName
            )
        );

        $contracts = $contractRepo->findContractByHoldingPropertyResident(
            $holding,
            $property,
            $residentId
        );

        $contract = $this->processContracts($contracts, $unitName);
        if ($contract) {
            return $contract;
        }

        $contractsWaiting = $this->em->getRepository('RjDataBundle:ContractWaiting')
            ->findByHoldingPropertyResident($holding, $property, $residentId);
        $contractWaiting = $this->processContracts($contractsWaiting, $unitName);

        if ($contractWaiting) {
            return $contractWaiting;
        }

        return null;
    }

    /**
     * @param array $contracts
     * @param string $unitName
     * @return null|Contract|ContractWaiting
     */
    public function processContracts(array $contracts, $unitName)
    {
        if (count($contracts) === 1) {
            $contract = reset($contracts);
            $this->logMessage(sprintf('We found contract with ID:%s', $contract->getId()));

            return $contract;
        }

        if (count($contracts) > 1) {
            $this->logMessage('We have more than 1 contract for this parameters');
            foreach ($contracts as $contract) {
                $unit = $contract->getUnit();
                if ($unit->getName() === $unitName) {
                    $this->logMessage(
                        sprintf(
                            'We found contract with ID:%s. But have multiple contract for this parameter',
                            $contract->getId()
                        )
                    );

                    return $contract;
                }
            }

            $failedMessage = sprintf(
                'We didn\'t find any contract with such unitName: %s in list.',
                $unitName
            );

            $this->logMessage($failedMessage);
            $this->logger->alert($failedMessage);
        }

        return null;
    }

    /**
     * @param GetResidentTransactionsLoginResponse $residentTransactions
     * @param Holding $holding
     * @param Property $property
     * @throws Exception
     */
    protected function processResidentTransactions(
        GetResidentTransactionsLoginResponse $residentTransactions,
        Holding $holding,
        Property $property
    ) {

        $residents = $residentTransactions->getProperty()->getCustomers();
        foreach ($residents as $resident) {
            $contract = $this->getContract($holding, $property, $resident);
            if (!$contract) {
                continue;
            }

            $balance = $this->calcResidentBalance($resident);
            $contract->setPaymentAccepted($resident->getPaymentAccepted());
            $this->logMessage(
                sprintf(
                    "Setup payment accepted to %s, for residentId %s",
                    $resident->getPaymentAccepted(),
                    $resident->getCustomerId()
                )
            );
            $contract->setIntegratedBalance($balance);
            $externalLeaseId = $contract->getExternalLeaseId();
            if (empty($externalLeaseId)) {
                $contract->setExternalLeaseId($resident->getLeaseId());
                $this->logMessage(
                    sprintf(
                        'Contract #%s externalLeaseId has been updated. ExternalLeaseId #%s',
                        $contract->getId(),
                        $resident->getLeaseId()
                    )
                );
            }
            $this->logMessage(
                sprintf(
                    "Contract #%s has been updated. Now the balance is $%s",
                    $contract->getId(),
                    $balance
                )
            );
        }
    }

    protected function calcResidentBalance(ResidentTransactionPropertyCustomer $resident)
    {
        $balance = 0;
        $transactions = $resident->getServiceTransactions()->getTransactions();

        foreach ($transactions as $transaction) {
            if ($transaction->getCharge()) {
                $balanceDue = $transaction->getCharge()->getDetail()->getBalanceDue();
            } else {
                $balanceDue = 0;
            }
            $balance += $balanceDue;
        }

        return $balance;
    }

    protected function logMessage($message)
    {
        if ($this->outputLogger) {
            $this->outputLogger->writeln($message);
        }
    }
}
