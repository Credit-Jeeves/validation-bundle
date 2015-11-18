<?php

namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportSynchronizeHPSCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('payment:report:synchronize')
            ->setDescription('Synchronizes payment process report w/ orders.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paymentProcessor = $this->getContainer()->get('payment_processor.heartland');
        $report = $paymentProcessor->loadReport();
        $result = $this->getContainer()->get('payment_processor.report_synchronizer')
            ->synchronize($report, $paymentProcessor);
        $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
    }
}
