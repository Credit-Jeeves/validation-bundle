<?php

namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorProfitStarsRdc;
use RentJeeves\CoreBundle\Command\BaseCommand;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterContractsToProfitStarsCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:payment-processor:profit-stars:register-contracts')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job')
            ->addArgument('deposit-account-id', InputArgument::REQUIRED, 'ID of deposit account')
            ->addArgument('page', InputArgument::REQUIRED, 'Page Number')
            ->addArgument('limit', InputArgument::REQUIRED, 'Limit per Page')
            ->setDescription('Registers all group contracts to a given location (deposit account) in ProfitStars');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DepositAccount $depositAccount */
        if (null === $depositAccount = $this->getDepositAccount($input->getArgument('deposit-account-id'))) {
            throw new \InvalidArgumentException(sprintf(
                'DepositAccount with id#%s not found',
                $input->getArgument('deposit-account-id')
            ));
        }

        $profitStarsSettings = $depositAccount->getHolding()->getProfitStarsSettings();
        if (true === empty($profitStarsSettings) || !$profitStarsSettings->getMerchantId()) {
            throw new \InvalidArgumentException(sprintf(
                'Can\'t register contracts to ProfitStars. Holding "%s" has no ProfitStars merchantId set',
                $depositAccount->getHolding()->getName()
            ));
        }

        $contracts = $this->getContracts(
            $depositAccount->getGroup(),
            $input->getArgument('page'),
            $input->getArgument('limit')
        );

        $exitCode = 0;
        foreach ($contracts as $contract) {
            try {
                $this->getProfitStarsPaymentProcessor()->registerContract($contract, $depositAccount);
            } catch (PaymentProcessorRuntimeException $e) {
                $this->getLogger()->alert(sprintf(
                    'ERROR: Contract #%d cannot be registered to DA #%d: %s',
                    $contract->getId(),
                    $depositAccount->getId(),
                    $e->getMessage()
                ));
                $exitCode = 1;
            }
        }

        $this->getLogger()->info(sprintf(
            'Registering contracts to ProfitStars for deposit account #%d finished %s',
            $depositAccount->getId(),
            $exitCode === 0 ? 'successfully' : 'with fail'
        ));

        return $exitCode;
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
     * @return PaymentProcessorProfitStarsRdc
     */
    protected function getProfitStarsPaymentProcessor()
    {
        return $this->getContainer()->get('payment_processor.profit_stars.rdc');
    }

    /**
     * @param Group $group
     * @param $page
     * @param $limit
     * @return Contract[]
     */
    protected function getContracts(Group $group, $page, $limit)
    {
        $this->getContainer()->get('soft.deleteable.control')->disable();

        return $this->getEntityManager()->getRepository('RjDataBundle:Contract')
            ->getActiveWithGroup($group, $page, $limit);
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->getContainer()->get('monolog.logger.profit_stars');
    }
}
