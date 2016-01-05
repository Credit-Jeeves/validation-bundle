<?php

namespace RentJeeves\CoreBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount;
use RentJeeves\CoreBundle\Exception\ContractMovementManagerException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * Service name "dedupe.contract_movement"
 */
class ContractMovementManager
{
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
     * @throws ContractMovementManagerException if we can`t move contract
     */
    public function move(Contract $contract, Unit $destinationUnit)
    {
        $sourceUnit = $contract->getUnit();
        if ($sourceUnit->getHolding() !== $destinationUnit->getHolding()) {
            $this->logger->warning(
                $message = sprintf(
                    'ERROR: srcUnit#%d and dstUnit#%d are in different holdings. ' .
                    'Cannot move contract#%d because external lease ID will be incorrect.',
                    $sourceUnit->getId(),
                    $destinationUnit->getId(),
                    $contract->getId()
                )
            );

            throw new ContractMovementManagerException($message);
        }

        $sourcePaymentProcessor = $sourceUnit->getGroup()->getGroupSettings()->getPaymentProcessor();
        $destinationPaymentProcessor = $destinationUnit->getGroup()->getGroupSettings()->getPaymentProcessor();
        if ($sourcePaymentProcessor !== $destinationPaymentProcessor) {
            $this->logger->warning(
                $message = sprintf(
                    'ERROR: we cannot move contracts to groups that use a different payment processor. ' .
                    '(srcUnit#%d and dstUnit#%d)',
                    $sourceUnit->getId(),
                    $destinationUnit->getId()
                )
            );

            throw new ContractMovementManagerException($message);
        }

        $tenant = $contract->getTenant();
        $externalResident = $this->findExternalResidentId($tenant, $contract->getHolding());
        if ($externalResident !== null && $contract->getGroupSettings()->isExternalResidentFollowsUnit() === true) {
            $this->logger->warning(
                $message = sprintf(
                    'ERROR: resident ID#%s for Tenant#%d follow units. We must resolve manually first.',
                    $externalResident->getResidentId(),
                    $tenant->getId()
                )
            );

            throw new ContractMovementManagerException($message);
        }

        if ($contract->getGroup() !== $destinationUnit->getGroup()) {
            $this->updateDepositAccountsForActivePayments($contract, $destinationUnit->getGroup());
        }

        if (false === $this->dryRunMode) {
            $contract->setUnit($destinationUnit);
            $contract->setProperty($destinationUnit->getProperty());
            $contract->setGroup($destinationUnit->getGroup());
            $this->em->flush();
        }

        $this->logger->info(sprintf('Contract#%d is updated.', $contract->getId()));
    }

    /**
     * @param boolean $dryRunMode
     */
    public function setDryRunMode($dryRunMode)
    {
        $this->dryRunMode = (boolean) $dryRunMode;
    }

    /**
     * @param Contract $contract
     * @param Group $group
     *
     * @throws ContractMovementManagerException Can not update active Payment or can not retokenize DepositAccount
     */
    protected function updateDepositAccountsForActivePayments(Contract $contract, Group $group)
    {
        $paymentProcessor = $this->paymentProcessorFactory->getPaymentProcessor($group);
        foreach ($this->findAllActivePaymentsByContract($contract) as $payment) {
            $paymentDepositAccount = $payment->getDepositAccount();
            $similarDepositAccount = $this->findSimilarDepositAccountForAnotherGroup($paymentDepositAccount, $group);
            if (null === $similarDepositAccount) {
                $this->logger->warning(
                    $message = sprintf(
                        'ERROR: %s Deposit Account for Group#%d(%s) not found. Can not update active Payment#%d.',
                        $paymentDepositAccount->getType(),
                        $group->getId(),
                        $group->getName(),
                        $payment->getId()
                    )
                );

                throw new ContractMovementManagerException($message);
            }

            $accountData = new PaymentAccount();
            $accountData->setEntity($payment->getPaymentAccount());
            try {
                if (false === $this->dryRunMode) {
                    $paymentProcessor->registerPaymentAccount($accountData, $similarDepositAccount);
                }
            } catch (\Exception $e) {
                $this->logger->warning(
                    $message = sprintf(
                        'ERROR: Could not retokenize DepositAccount#%d : %s',
                        $similarDepositAccount->getId(),
                        $e->getMessage()
                    )
                );

                throw new ContractMovementManagerException($message);
            }

            $payment->setDepositAccount($similarDepositAccount);
        }
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
        return $this->em->getRepository('RjDataBundle:Payment')->findAllActivePaymentsForContract($contract);
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
