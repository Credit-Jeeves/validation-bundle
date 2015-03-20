<?php

namespace RentJeeves\CheckoutBundle\Command;

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

        $paymentProcessor = $this->getContainer()->get('payment_processor.heartland');
        if ($report = $paymentProcessor->loadReport($syncType, ['make_archive' => $makeArchive])) {
            $result = $this->getContainer()->get('payment_processor.report_synchronizer')->synchronize($report);
            $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
        } else {
            $output->writeln('Report not found');
        }
    }
}
