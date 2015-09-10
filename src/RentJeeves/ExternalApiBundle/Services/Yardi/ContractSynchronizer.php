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
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Model\Yardi\RtCustomer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseChargesLoginResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionTransactions;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;

/**
 * @DI\Service("yardi.contract_sync")
 */
class ContractSynchronizer
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

    public function syncBalance()
    {
        try {
            $holdings = $this->getHoldingsForUpdatingBalance();
            if (empty($holdings)) {
                return $this->logger->info('YardiBalanceSync: No data to update');
            }

            foreach ($holdings as $holding) {
                $this->updateBalancesForHolding($holding);
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);

            return $this->logger->alert(sprintf('YardiBalanceSync ERROR: %s', $e->getMessage()));
        }
    }

    public function syncRecurringCharge()
    {
        $holdings = $this->getHoldingsSyncRecurringCharges();
        if (empty($holdings)) {
            $this->logMessage('Yardi Sync Recurring Charge: No data to update.');
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
        $this->logMessage(sprintf('Yardi sync Recurring Charge: start work with holding %d', $holding->getId()));
        $propertyMappingRepository = $this->em->getRepository('RjDataBundle:ResidentMapping');
        $this->residentDataManager->setSettings($holding->getExternalSettings());
        $countPropertyMappingSets = ceil(
            $propertyMappingRepository->getCountUniqueByHolding($holding) / self::COUNT_PROPERTIES_PER_SET
        );

        $this->logMessage(sprintf('Found %d pages of property mappings', $countPropertyMappingSets));

        for ($offset = 1; $offset <= $countPropertyMappingSets; $offset++) {
            $this->logMessage(sprintf('Open %d page of property mappings', $offset));

            $propertyMappings = $propertyMappingRepository->findUniqueByHolding(
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
        /** @var $residentClient ResidentTransactionsClient */
        $residentClient = $this->clientFactory->getClient(
            $propertyMapping->getHolding()->getYardiSettings(),
            SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS
        );

        $this->logMessage(
            sprintf(
                'Yardi sync Recurring Charge: start work with propertyMapping \'%s\'',
                $propertyMapping->getExternalPropertyId()
            )
        );

        try {
            $residentTransactions = $residentClient->getResidentLeaseCharges(
                $propertyMapping->getExternalPropertyId()
            );
        } catch (\Exception $e) {
            $this->logMessage(
                sprintf(
                    'Yardi sync Recurring Charge: \'%s\'',
                    $e->getMessage()
                ),
                500
            );

            return;
        }

        if ($residentTransactions instanceof ResidentLeaseChargesLoginResponse &&
            count($residentTransactions->getProperty()->getCustomers()) > 0) {

            foreach ($residentTransactions->getProperty()->getCustomers() as $customer) {
                $this->updateContractsRentForResidentTransaction($propertyMapping, $customer);
            }

            return;
        }

        $this->logMessage(
            sprintf(
                'Yardi sync Recurring Charge: Empty response for Property %s of Holding#%d',
                $propertyMapping->getExternalPropertyId(),
                $propertyMapping->getHolding()->getId()
            ),
            500
        );
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param RtCustomer $customer
     */
    public function updateContractsRentForResidentTransaction(PropertyMapping $propertyMapping, RtCustomer $customer)
    {

        $recurringCodes = $propertyMapping->getHolding()->getRecurringCodesArray();
        $serviceTransactions = $customer->getRtServiceTransactions();
        $transactions = $serviceTransactions->getTransactions();
        $amount = 0;
        /** @var ResidentTransactionTransactions $transaction */
        foreach ($transactions as $transaction) {
            $charge = $transaction->getCharge();
            if (!in_array($charge->getDetail()->getChargeCode(), $recurringCodes)) {
                $this->logger->info(
                    sprintf(
                        'Charge code(%s) not contains in list %s',
                        $charge->getDetail()->getChargeCode(),
                        $propertyMapping->getHolding()->getRecurringCodes()
                    )
                );
                continue;
            }

            $residentId = $charge->getDetail()->getCustomerID();
            $unitName = $charge->getDetail()->getUnitID();
            $amount += $charge->getDetail()->getAmount();
        }

        if (empty($residentId) || empty($unitName) || $amount === 0) {
            return;
        }

        $contract = $this->getContract($propertyMapping, $residentId, $unitName);

        if (empty($contract)) {
            $this->logger->info('Yardi synch rent: empty contract.');

            return;
        }

        $contract->setRent($amount);
        $this->em->persist($contract);
    }

    /**
     * @param Holding $holding
     * @throws Exception
     */
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
                    $this->processResidentTransactions($residentTransactions, $propertyMapping);
                } else {
                    $this->logger->alert(sprintf(
                        'YardiBalanceSync ERROR: Could not load resident transactions for property %s, holding %s: %s',
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

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding[]
     */
    public function getHoldingsSyncRecurringCharges()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForSyncRecurringChargesYardi();
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding[]
     */
    protected function getHoldingsForUpdatingBalance()
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsForUpdatingBalanceYardi();
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param string $residentId
     * @param string $unitName
     * @return Contract
     * @throws Exception
     */
    protected function getContract(PropertyMapping $propertyMapping, $residentId, $unitName)
    {
        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');
        $holding = $propertyMapping->getHolding();
        $property = $propertyMapping->getProperty();
        $this->logger->info(
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
            $this->logger->info(sprintf('Found contract with ID:%s', $contract->getId()));

            return $contract;
        }

        if (count($contracts) > 1) {
            $this->logger->info('Found more than 1 contract for this parameters');
            foreach ($contracts as $contract) {
                $unit = $contract->getUnit();
                if ($unit->getName() === $unitName) {
                    $this->logger->info(
                        sprintf(
                            'Found contract with ID:%s.',
                            $contract->getId()
                        )
                    );

                    return $contract;
                }
            }

            $this->logger->alert(sprintf('YardiBalanceSync: Contract with unitName %s not found.', $unitName));
        }

        return null;
    }

    /**
     * @param GetResidentTransactionsLoginResponse $residentTransactions
     * @param PropertyMapping $propertyMapping
     * @throws Exception
     */
    protected function processResidentTransactions(
        GetResidentTransactionsLoginResponse $residentTransactions,
        PropertyMapping $propertyMapping
    ) {
        $holding = $propertyMapping->getHolding();
        $property = $propertyMapping->getProperty();
        $residents = $residentTransactions->getProperty()->getCustomers();
        $propertyMapping = $property->getPropertyMappingByHolding($holding); //@TODO move above and put PropertyMapping
        foreach ($residents as $resident) {
            $residentId = $resident->getCustomerId();
            $unitName = $resident->getUnit()->getUnitId();
            $contract = $this->getContract($propertyMapping, $residentId, $unitName);
            if (!$contract) {
                continue;
            }

            $balance = $this->calcResidentBalance($resident);
            $contract->setPaymentAccepted($resident->getPaymentAccepted());
            $this->logger->info(
                sprintf(
                    'YardiBalanceSync: Setup payment accepted to %s, for residentId %s',
                    $resident->getPaymentAccepted(),
                    $resident->getCustomerId()
                )
            );
            $contract->setIntegratedBalance($balance);
            $externalLeaseId = $contract->getExternalLeaseId();
            if (empty($externalLeaseId)) {
                $contract->setExternalLeaseId($resident->getLeaseId());
                $this->logger->info(
                    sprintf(
                        'Contract #%s externalLeaseId has been updated. ExternalLeaseId #%s',
                        $contract->getId(),
                        $resident->getLeaseId()
                    )
                );
            }
            $this->logger->info(
                sprintf(
                    'Contract #%s has been updated. Now the balance is $%s',
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
}
