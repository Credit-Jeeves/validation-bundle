<?php

namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportSynchronizeAciPayAnyoneCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('payment:report:synchronize:aci:payanyone')
            ->setDescription('Synchronizes ACIPayAnyone report w/ orders');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $report = $this->getAciPayAnyonePaymentProcessor()->loadReport();
        $result = $this->getReportSynchronizer()->synchronize($report, "ACIPayAnyone", false);
        $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
    }

    /**
     * @return PaymentProcessorAciPayAnyone
     */
    protected function getAciPayAnyonePaymentProcessor()
    {
        return $this->getContainer()->get('payment_processor.aci_pay_anyone');
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReportSynchronizer
     */
    protected function getReportSynchronizer()
    {
        return $this->getContainer()->get('payment_processor.report_synchronizer');
    }
}
