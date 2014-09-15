<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
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
    protected $em;

    protected $clientFactory;

    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     * })
     */
    public function __construct(EntityManager $em, SoapClientFactory $clientFactory)
    {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
    }

    public function run()
    {
        $holdings = $this->getHoldings();
        if (empty($holdings)) {
            return $this->logMessage('No data to update');
        }

        foreach ($holdings as $holding) {
            $this->updateBalancesForHolding($holding);
        }
    }

    public function usingOutput(OutputInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    protected function updateBalancesForHolding(Holding $holding)
    {
        $propertyRepo = $this->em->getRepository('RjDataBundle:Property');

        /** @var $residentClient ResidentClient */
        $residentClient = $this->clientFactory->getClient($holding->getYardiSettings(), SoapClientEnum::RESIDENT);
        $properties = $propertyRepo->findContractPropertiesByHolding($holding);
        /** @var $property Property */
        foreach ($properties as $property) {
            $mapping = $property->getPropertyMapping()->first();
            $residentTransactions = $residentClient->getResidentTransactions($mapping->getLandlordPropertyId());
            $this->processResidentTransactions($residentTransactions, $holding, $property);
        }
        $this->em->flush();
    }

    protected function getHoldings()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalance();
    }

    protected function getContracts(Holding $holding, Property $property, ResidentTransactionPropertyCustomer $resident)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');

        return $contractRepo->findContractByHoldingPropertyResidentUnit(
            $holding,
            $property,
            $resident->getCustomerId(),
            $resident->getUnit()->getUnitId()
        );

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

            $contracts = $this->getContracts($holding, $property, $resident);
            if (empty($contracts)) {
                $this->logMessage(
                    sprintf(
                        "Could not find contract with property %s, unit %s, resident %s",
                        $property->getPropertyMapping()->first()->getLandlordPropertyId(),
                        $unitId,
                        $residentId
                    )
                );
                continue;
            }
            if (count($contracts) > 1) {
                $this->logMessage(
                    sprintf(
                        "Found more than one contract with property %s, unit %s, resident %s",
                        $property->getPropertyMapping()->first()->getLandlordPropertyId(),
                        $unitId,
                        $residentId
                    )
                );
                continue;
            }
            /** @var Contract $contract */
            $contract = reset($contracts);
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
