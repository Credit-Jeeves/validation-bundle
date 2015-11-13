<?php

namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportSynchronizeAciCollectV4Command extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('payment:report:synchronize:aci:collectv4')
            ->setDescription('Synchronizes ACIPayCollectV4 report w/ orders');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paymentProcessor = $this->getAciPaymentProcessor();
        $report = $paymentProcessor->loadReport();
        $result = $this->getReportSynchronizer()->synchronize($report, $paymentProcessor, false);
        $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay
     */
    protected function getAciPaymentProcessor()
    {
        return $this->getContainer()->get('payment_processor.aci_collect_pay');
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReportSynchronizer
     */
    protected function getReportSynchronizer()
    {
        return $this->getContainer()->get('payment_processor.report_synchronizer');
    }
}
