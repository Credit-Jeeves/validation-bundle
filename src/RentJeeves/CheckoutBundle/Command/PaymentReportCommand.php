<?php

namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('Payment:synchronize')
            ->setDescription('Synchronizes hps payment report w/ orders.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = __DIR__ . '/report.csv';
        $data = $this
            ->getContainer()
            ->get('payment.report')
            ->synchronize($filename);
    }
} 
