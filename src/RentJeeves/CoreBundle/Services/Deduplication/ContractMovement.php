<?php

namespace RentJeeves\CoreBundle\Services\Deduplication;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\PaymentStatus;

class ContractMovement
{
    const LOG_PREFIX = '[ContractMovement]';
    
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var PaymentProcessorFactory
     */
    protected $paymentProcessorFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $dryRunMode = false;

    /**
     * @param EntityManagerInterface $em
     * @param PaymentProcessorFactory $factory
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $em, PaymentProcessorFactory $factory, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->paymentProcessorFactory = $factory;
        $this->logger = $logger;
    }

    /**
     * Move contracts from one unit to another
     *
     * @param Contract $contract
     * @param Unit $destinationUnit
     *
     * @return boolean
     */
    public function move(Contract $contract, Unit $destinationUnit)
    {
        $sourceUnit = $contract->getUnit();
        if ($sourceUnit->getHolding() !== $destinationUnit->getHolding()) {
            $this->logger->warning(
                sprintf(
                    '%s: ERROR: srcUnit#%d and dstUnit#%d are if different holdings. ' .
                    'Cannot move contract#%d because external lease ID will be incorrect.',
                    self::LOG_PREFIX,
                    $sourceUnit->getId(),
                    $destinationUnit->getId(),
                    $contract->getId()
                )
            );

            return false;
        }

        $sourcePaymentProcessor = $sourceUnit->getGroup()->getGroupSettings()->getPaymentProcessor();
        $destinationPaymentProcessor = $destinationUnit->getGroup()->getGroupSettings()->getPaymentProcessor();
        if ($sourcePaymentProcessor !== $destinationPaymentProcessor) {
            $this->logger->warning(
                sprintf(
                    '%s: ERROR: we cannot move contracts to groups that use a different payment processor.',
                    self::LOG_PREFIX
                )
            );

            return false;
        }

        $tenant = $contract->getTenant();
        $externalResidentId = $this->findExternalResidentId($tenant, $contract->getHolding());
        if ($externalResidentId !== null && $contract->getGroupSettings()->isExternalResidentFollowsUnit() === true) {
            $this->logger->warning(
                sprintf(
                    '%s: ERROR: resident ID follow units. We must resolve manually first.',
                    self::LOG_PREFIX
                )
            );

            return false;
        }

        if (false === $this->updateDepositAccountsForActivePayments($contract, $destinationUnit->getGroup())) {
            return false;
        }

        if (false === $this->dryRunMode) {
            $contract->setUnit($destinationUnit);
            $contract->setProperty($destinationUnit->getProperty());
            $contract->setGroup($destinationUnit->getGroup());
            $contract->setHolding($destinationUnit->getHolding());
            $this->em->flush();
        }

        $this->logger->info(sprintf('%s: Contract#%d is updated.', self::LOG_PREFIX, $contract->getId()));

        return true;
    }

    /**
     * @param Contract $contract
     * @param Group $group
     *
     * @return boolean
     */
    protected function updateDepositAccountsForActivePayments(Contract $contract, Group $group)
    {
        $paymentProcessor = $this->paymentProcessorFactory->getPaymentProcessor($group);
        foreach ($this->findAllActivePaymentsByContract($contract) as $payment) {
            $paymentDepositAccount = $payment->getDepositAccount();
            $similarDepositAccount = $this->findSimilarDepositAccountForAnotherGroup($paymentDepositAccount, $group);
            if (null === $similarDepositAccount) {
                $this->logger->warning(
                    sprintf(
                        '%s: ERROR: %s Deposit Account for Group#%d(%s) not found. Can not update active Payment#%d.',
                        self::LOG_PREFIX,
                        $paymentDepositAccount->getType(),
                        $group->getId(),
                        $group->getName(),
                        $payment->getId()
                    )
                );

                return false;
            }

            $accountData = new PaymentAccount();
            $accountData->setEntity($payment->getPaymentAccount());
            try {
                if (false === $this->dryRunMode) {
                    $paymentProcessor->registerPaymentAccount($accountData, $similarDepositAccount);
                }
            } catch (\Exception $e) {
                $this->logger->warning(
                    sprintf(
                        '%s: ERROR: Could not retokenize DepositAccount#%d : %s',
                        self::LOG_PREFIX,
                        $similarDepositAccount->getId(),
                        $e->getMessage()
                    )
                );

                return false;
            }

            $payment->setDepositAccount($similarDepositAccount);
        }

        return true;
    }

    /**
     * @param boolean $dryRunMode
     */
    public function setDryRunMode($dryRunMode)
    {
        $this->dryRunMode = (boolean) $dryRunMode;
    }

    /**
     * @param Tenant $tenant
     * @param Holding $holding
     *
     * @return null|\RentJeeves\DataBundle\Entity\ResidentMapping
     */
    protected function findExternalResidentId(Tenant $tenant, Holding $holding)
    {
        return $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            [
                'tenant' => $tenant,
                'holding' => $holding
            ]
        );
    }

    /**
     * @param Contract $contract
     *
     * @return \RentJeeves\DataBundle\Entity\Payment[]
     */
    protected function findAllActivePaymentsByContract(Contract $contract)
    {
        return $this->em->getRepository('RjDataBundle:Payment')->findBy(
            [
                'contract' => $contract,
                'status' => PaymentStatus::ACTIVE
            ]
        );
    }

    /**
     * @param DepositAccount $depositAccount
     * @param Group $group
     *
     * @return null|DepositAccount
     */
    protected function findSimilarDepositAccountForAnotherGroup(DepositAccount $depositAccount, Group $group)
    {
        return $this->em->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            [
                'type' => $depositAccount->getType(),
                'paymentProcessor' => $depositAccount->getPaymentProcessor(),
                'group' => $group,
                'status' => DepositAccountStatus::DA_COMPLETE
            ]
        );
    }
}
