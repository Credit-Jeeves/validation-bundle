<?php

namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\CoreBundle\Enum;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Command\BaseCommand;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateActivePaymentsCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('payment-processor:migrate-active-payments')
            ->addOption(
                'holding-id',
                null,
                InputOption::VALUE_REQUIRED,
                'holding id'
            )
            ->addOption(
                'to-processor',
                null,
                InputOption::VALUE_OPTIONAL,
                'target payment processor: ACI or Heartland',
                PaymentProcessor::ACI
            )
            ->addOption(
                'from-processor',
                null,
                InputOption::VALUE_OPTIONAL,
                'current payment processor: ACI or Heartland',
                PaymentProcessor::HEARTLAND
            )
            ->setDescription(
                'Closes current payments and duplicates their data to new active payments
                with payment accounts of the target payment processor.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start migrating payments...</info>');

        if (false == $input->getOption('holding-id')) {
            throw new \InvalidArgumentException('Please, specify holding id!');
        }

        if (false == $holding = $this->getHolding($input->getOption('holding-id'))) {
            throw new \InvalidArgumentException(
                sprintf('Holding with id #%s not found.', $input->getOption('holding-id'))
            );
        }
        $paymentProcessorFrom = $input->getOption('from-processor');
        if (false == PaymentProcessor::isValid($paymentProcessorFrom)) {
            throw new \InvalidArgumentException(
                sprintf('Specified payment processor \'%s\' to migrate from is not found.', $paymentProcessorFrom)
            );
        }

        $paymentProcessorTo = $input->getOption('to-processor');
        if (false == PaymentProcessor::isValid($paymentProcessorTo)) {
            throw new \InvalidArgumentException(
                sprintf('Specified payment processor \'%s\' to migrate to is not found.', $paymentProcessorTo)
            );
        }
        if ($paymentProcessorFrom === $paymentProcessorTo) {
            throw new \InvalidArgumentException(
                sprintf('Specified payment processors TO and FROM are identical.')
            );
        }

        $em = $this->getEntityManager();

        try {
            $activePayments = $em->getRepository('RjDataBundle:Payment')
                ->getActiveHoldingPaymentsWithPaymentProcessor($holding, $paymentProcessorFrom);
            $output->writeln(sprintf(
                'Found %d active payments for \'%s\'.',
                count($activePayments),
                $paymentProcessorFrom
            ));

            /** @var Payment $activePayment */
            foreach ($activePayments as $activePayment) {
                $output->writeln('');
                $output->writeln(sprintf('Processing active payment #%d.', $activePayment->getId()));
                $newPaymentAccount = $this->getNewPaymentAccount(
                    $paymentProcessorFrom,
                    $activePayment->getPaymentAccount()
                );
                if (null === $newPaymentAccount) {
                    $output->writeln(sprintf(
                        'New Payment account with payment processor %s for Payment Account#\'%d\' not found.',
                        $paymentProcessorTo,
                        $activePayment->getPaymentAccount()->getId()
                    ));
                    continue;
                }
                $output->writeln(sprintf(
                    'Found \'%s\' payment account #%d.',
                    $paymentProcessorTo,
                    $newPaymentAccount->getId()
                ));

                $newDepositAccount = $em->getRepository('RjDataBundle:DepositAccount')->findOneBy(
                    [
                        'paymentProcessor' => $paymentProcessorTo,
                        'type' => $activePayment->getDepositAccount()->getType(),
                        'group' => $activePayment->getContract()->getGroup(),
                        'status' => DepositAccountStatus::DA_COMPLETE
                    ]
                );
                if (!$newDepositAccount) {
                    $output->writeln(sprintf(
                        'Deposit account for payment processor \'%s\' of type \'%s\' not found.',
                        $paymentProcessorTo,
                        $activePayment->getDepositAccount()->getType()
                    ));
                    continue;
                }
                $output->writeln(sprintf(
                    'Found \'%s\' deposit account #%d.',
                    $paymentProcessorTo,
                    $newDepositAccount->getId()
                ));

                $newPayment = new Payment();
                $newPayment->setContract($activePayment->getContract());
                $newPayment->setType($activePayment->getType());
                $newPayment->setPaymentAccount($newPaymentAccount);
                $newPayment->setAmount($activePayment->getAmount());
                $newPayment->setTotal($activePayment->getTotal());
                $newPayment->setPaidFor($activePayment->getPaidFor());
                $newPayment->setDueDate($activePayment->getDueDate());
                $newPayment->setStartMonth($activePayment->getStartMonth());
                $newPayment->setStartYear($activePayment->getStartYear());
                $newPayment->setEndMonth($activePayment->getEndMonth());
                $newPayment->setEndYear($activePayment->getEndYear());
                $newPayment->setDepositAccount($newDepositAccount);
                $newPayment->setActive();

                $activePayment->setClosed($this, PaymentCloseReason::PAYMENT_PROCESSOR_MIGRATION);

                $em->persist($newPayment);
                $em->flush();
                $output->writeln(sprintf('Old payment #%d has been closed.', $activePayment->getId()));
                $output->writeln(sprintf('New payment #%d has been created.', $newPayment->getId()));
            }
            $output->writeln('<info>All payments have been successfully migrated!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Something went wrong. %s</error>>', $e->getMessage()));
        }
    }

    /**
     * @param string $paymentProcessorFrom
     * @param PaymentAccount $activePaymentAccount
     *
     * @return PaymentAccount|null
     */
    protected function getNewPaymentAccount($paymentProcessorFrom, PaymentAccount $activePaymentAccount)
    {
        if ($paymentProcessorFrom === PaymentProcessor::HEARTLAND) {
            $paymentAccountMigration = $this->getEntityManager()
                ->getRepository('RjDataBundle:PaymentAccountMigration')->findOneBy(
                    [
                        'heartlandPaymentAccount' => $activePaymentAccount,
                    ]
                );

            return $paymentAccountMigration === null ?: $paymentAccountMigration->getAciPaymentAccount();
        } else {
            $paymentAccountMigration = $this->getEntityManager()
                ->getRepository('RjDataBundle:PaymentAccountMigration')->findOneBy(
                    [
                        'aciPaymentAccount' => $activePaymentAccount,
                    ]
                );

            return $paymentAccountMigration === null ?: $paymentAccountMigration->getHeartlandPaymentAccount();
        }
    }

    /**
     * @param int $holdingId
     * @return Holding
     */
    protected function getHolding($holdingId)
    {
        return $this->getEntityManager()->find('DataBundle:Holding', $holdingId);
    }
}
