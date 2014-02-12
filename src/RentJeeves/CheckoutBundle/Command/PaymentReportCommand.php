<?php

namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('Payment:synchronize')
            ->setDescription('Synchronizes hps payment report w/ orders.')
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
        $makeArchive = $input->getOption('archive');

        $result = $this
            ->getContainer()
            ->get('payment.report')
            ->synchronize($makeArchive);

        $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
    }
}
