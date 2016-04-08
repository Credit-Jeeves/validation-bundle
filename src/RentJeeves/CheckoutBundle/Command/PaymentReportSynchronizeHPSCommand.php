<?php

namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportSynchronizeHPSCommand extends BaseCommand
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
        try {
            $paymentProcessor = $this->getContainer()->get('payment_processor.heartland');
            $report = $paymentProcessor->loadReport();
            $result = $this->getContainer()->get('payment_processor.report_synchronizer')
                ->synchronize($report, $paymentProcessor);
            $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
        } catch (\Exception $e) {
            $this->getLogger()->emergency(
                sprintf('PaymentReportSynchronizeHPSCommand finished with error : %s', $e->getMessage())
            );
        }
    }
}
