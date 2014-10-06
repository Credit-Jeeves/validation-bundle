<?php

namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\DataBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('Payment:synchronize')
            ->setDescription('Synchronizes hps payment report w/ orders.')
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'What sync would you like to run? If ACH deposit, add "deposit", if payment reversal, add "reversal"'
            )
            ->addOption(
                'archive',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the original report is archived.',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $syncType = $input->getArgument('type');
        $makeArchive = $input->getOption('archive');
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        switch ($syncType) {
            case 'deposit':
                $result = $this
                    ->getContainer()
                    ->get('payment.deposit_report')
                    ->synchronize($makeArchive);
                $output->writeln(sprintf('Amount of synchronized deposits: %s', $result));

                break;
            case 'reversal':
                $result = $this
                    ->getContainer()
                    ->get('payment.reversal_report')
                    ->synchronize($makeArchive);
                $output->writeln(sprintf('Amount of synchronized reversal payments: %s', $result));

                break;
            default:
                $output->writeln(sprintf('Unknown sync type "%s". Choose "deposit" or "reversal".', $syncType));
        }
    }
}
