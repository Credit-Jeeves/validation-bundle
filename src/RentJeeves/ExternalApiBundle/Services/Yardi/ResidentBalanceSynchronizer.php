<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Soap\SoapClientEnum;
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

    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     *     "exceptionCatcher" = @DI\Inject("fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(EntityManager $em, SoapClientFactory $clientFactory, ExceptionCatcher $exceptionCatcher)
    {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
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
        $this->logger = $logger;

        return $this;
    }

    protected function updateBalancesForHolding(Holding $holding)
    {
        $repo = $this->em->getRepository('RjDataBundle:Property');

        /** @var $residentClient ResidentClient */
        $residentClient = $this->clientFactory->getClient($holding->getYardiSettings(), SoapClientEnum::RESIDENT);
        $propertySets = ceil($repo->countContractPropertiesByHolding($holding) / self::COUNT_PROPERTIES_PER_SET);
        for ($offset = 1; $offset <= $propertySets; $offset++) {
            $properties = $repo->findContractPropertiesByHolding($holding, $offset, self::COUNT_PROPERTIES_PER_SET);
            /** @var $property Property */
            foreach ($properties as $property) {
                $mapping = $property->getPropertyMapping()->first();
                $residentTransactions = $residentClient->getResidentTransactions($mapping->getExternalPropertyId());
                $this->processResidentTransactions($residentTransactions, $holding, $property);
            }
            $this->em->flush();
            $this->em->clear();
        }
    }

    protected function getHoldings()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalance();
    }

    protected function getContract(Holding $holding, Property $property, ResidentTransactionPropertyCustomer $resident)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $residentId = $resident->getCustomerId();
        $unitName = $resident->getUnit()->getUnitId();

        $contracts = $contractRepo->findContractByHoldingPropertyResidentUnit(
            $holding,
            $property,
            $residentId,
            $unitName
        );

        if (count($contracts) > 1) {
            $this->logMessage(
                sprintf(
                    "Found more than one contract with property %s, unit %s, resident %s",
                    $property->getPropertyMapping()->first()->getExternalPropertyId(),
                    $unitName,
                    $residentId
                )
            );
            return null;
        }

        if (count($contracts) == 1) {
            $contract = reset($contracts);
            return $contract;
        }

        $contractWaiting = $this->em->getRepository('RjDataBundle:ContractWaiting')
            ->findByHoldingPropertyUnitResident($holding, $property, $unitName, $residentId);
        if ($contractWaiting) {
            return $contractWaiting;
        }

        $this->logMessage(
            sprintf(
                "Could not find contract with property %s, unit %s, resident %s",
                $property->getPropertyMapping()->first()->getExternalPropertyId(),
                $unitName,
                $residentId
            )
        );

        return null;
    }

    protected function processResidentTransactions(
        GetResidentTransactionsLoginResponse $residentTransactions,
        Holding $holding,
        Property $property
    ) {

        $residents = $residentTransactions->getProperty()->getCustomers();
        foreach ($residents as $resident) {
            $residentId = $resident->getCustomerId();
            $unitId = $resident->getUnit()->getUnitId();

            $contract = $this->getContract($holding, $property, $resident);
            if (!$contract) {
                continue;
            }

            $balance = $this->calcResidentBalance($resident);
            $contract->setIntegratedBalance($balance);
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
            $balance += $transaction->getCharge()->getDetail()->getBalanceDue();
        }

        return $balance;
    }

    protected function logMessage($message)
    {
        if ($this->logger) {
            $this->logger->writeln($message);
        }
    }
}
