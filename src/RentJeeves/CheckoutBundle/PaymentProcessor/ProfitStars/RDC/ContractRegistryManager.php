<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\ProfitStarsRegisteredContract;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\PaymentVaultClient;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\RegisterCustomerResponse;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\ReturnValue;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\WSCustomer as ProfitStarsCustomer;

/**
 * Service name "payment_processor.profit_stars.rdc.contract_registry"
 */
class ContractRegistryManager
{
    /** @var PaymentVaultClient */
    protected $client;

    /** @var EntityManager */
    protected $em;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Skip32IdEncoder */
    protected $encoder;

    /** @var string */
    protected $storeId;

    /** @var string */
    protected $storeKey;

    /**
     * @param PaymentVaultClient $client
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param Skip32IdEncoder $encoder
     * @param string $rentTrackStoreId
     * @param string $rentTrackStoreKey
     */
    public function __construct(
        PaymentVaultClient $client,
        EntityManager $em,
        LoggerInterface $logger,
        Skip32IdEncoder $encoder,
        $rentTrackStoreId,
        $rentTrackStoreKey
    ) {
        $this->client = $client;
        $this->em = $em;
        $this->logger = $logger;
        $this->encoder = $encoder;
        $this->storeId = $rentTrackStoreId;
        $this->storeKey = $rentTrackStoreKey;
    }

    /**
     * @param Contract $contract
     * @param DepositAccount $depositAccount
     */
    public function registerContract(Contract $contract, DepositAccount $depositAccount)
    {
        if (false === $contract->hasProfitStarsRegisteredLocation($depositAccount->getMerchantName())) {
            $this->logger->debug(
                sprintf(
                    'Try to register contract #%d for location #%s',
                    $contract->getId(),
                    $depositAccount->getMerchantName()
                )
            );

            $tenant = $contract->getTenant();
            $address = $contract->getProperty()->getPropertyAddress();
            $customer = new ProfitStarsCustomer();
            $customer
                ->setEntityId($depositAccount->getMerchantName())
                ->setIsCompany(false)
                ->setCustomerNumber($this->getCustomerNumber($contract))
                ->setFirstName($tenant->getFirstName())
                ->setLastName($tenant->getLastName())
                ->setEmail($tenant->getEmail())
                ->setAddress1($address->getAddress())
                ->setAddress2($contract->getUnit()->getName())
                ->setCity($address->getCity())
                ->setStateRegion($address->getState())
                ->setPostalCode($address->getZip());
            $entityId = $contract->getHolding()->getProfitStarsSettings()->getMerchantId();
            try {
                $response = $this->client->RegisterCustomer($this->storeId, $this->storeKey, $entityId, $customer);
            } catch (\Exception $e) {
                $this->logger->alert(sprintf(
                    'Can not register contract #%d. Reason: %s',
                    $contract->getId(),
                    $e->getMessage()
                ));
                throw new PaymentProcessorRuntimeException($e->getMessage());
            }

            if ($response instanceof RegisterCustomerResponse &&
                ReturnValue::SUCCESS === $response->getRegisterCustomerResult()->getReturnValue()
            ) {
                $this->saveRegisteredContract($contract, $depositAccount);
            } else {
                $this->logger->alert($message = sprintf(
                    'Got failed response when registering contract #%d for location #%s',
                    $contract->getId(),
                    $depositAccount->getMerchantName()
                ));
                throw new PaymentProcessorRuntimeException($message);
            }
        } else {
            $this->logger->debug(
                sprintf(
                    'Contract #%d is already registered for location #%d',
                    $contract->getId(),
                    $depositAccount->getMerchantName()
                )
            );
        }
    }

    /**
     * @param Contract $contract
     * @return string
     */
    public function getCustomerNumber(Contract $contract)
    {
        return $this->encoder->encode($contract->getId());
    }

    /**
     * @param Contract $contract
     * @param DepositAccount $depositAccount
     */
    protected function saveRegisteredContract(Contract $contract, DepositAccount $depositAccount)
    {
        try {
            $registeredContract = new ProfitStarsRegisteredContract();
            $registeredContract->setContract($contract);
            $registeredContract->setLocationId($depositAccount->getMerchantName());
            $contract->addProfitStarsRegisteredContract($registeredContract);
            $this->em->persist($registeredContract);
            $this->em->flush($registeredContract);
        } catch (DBALException $e) {
            $this->logger->alert(sprintf(
                'Can not save to DB registered contract #%d. Reason: %s',
                $contract->getId(),
                $e->getMessage()
            ));
            throw new PaymentProcessorRuntimeException($e->getMessage());
        }
    }
}
