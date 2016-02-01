<?php

namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorProfitStarsRdc;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterContractToProfitStarsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:payment-processor:profit-stars:register-contract')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job')
            ->addArgument('contract-id', InputArgument::REQUIRED, 'ID of contract')
            ->addArgument('deposit-account-id', InputArgument::REQUIRED, 'ID of deposit account')
            ->setDescription('Registers contract to a given location (deposit account) in ProfitStars');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Contract $contract */
        if (null === $contract = $this->getContract($input->getArgument('contract-id'))) {
            throw new \InvalidArgumentException(sprintf(
                'Contract with id#%s not found',
                $input->getArgument('contract-id')
            ));
        }

        /** @var DepositAccount $depositAccount */
        if (null === $depositAccount = $this->getDepositAccount($input->getArgument('deposit-account-id'))) {
            throw new \InvalidArgumentException(sprintf(
                'DepositAccount with id#%s not found',
                $input->getArgument('deposit-account-id')
            ));
        }

        $profitStarsSettings = $contract->getHolding()->getProfitStarsSettings();
        if (true === empty($profitStarsSettings) || !$profitStarsSettings->getMerchantId()) {
            throw new \InvalidArgumentException(sprintf(
                'Can\'t register contract #%d. Holding "%s" has no ProfitStars merchantId set',
                $contract->getId(),
                $contract->getHolding()->getName()
            ));
        }

        $this->getProfitStarsPaymentProcessor()->registerContract($contract, $depositAccount);

        return 0;
    }

    /**
     * @param $contractId
     * @return Contract|null
     */
    protected function getContract($contractId)
    {
        return $this->getEntityManager()->find('RjDataBundle:Contract', $contractId);
    }

    /**
     * @param $depositAccountId
     * @return DepositAccount|null
     */
    protected function getDepositAccount($depositAccountId)
    {
        return $this->getEntityManager()->find('RjDataBundle:DepositAccount', $depositAccountId);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return PaymentProcessorProfitStarsRdc
     */
    protected function getProfitStarsPaymentProcessor()
    {
        return $this->getContainer()->get('payment_processor.profit_stars.rdc');
    }
}
