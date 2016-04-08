<?php

namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportSynchronizeAciCollectV4Command extends BaseCommand
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
        try {
            $paymentProcessor = $this->getAciPaymentProcessor();
            $report = $paymentProcessor->loadReport();
            $result = $this->getReportSynchronizer()->synchronize($report, $paymentProcessor, true);
            $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
        } catch (\Exception $e) {
            $this->getLogger()->emergency(
                sprintf('PaymentReportSynchronizeAciCollectV4Command finished with error : %s', $e->getMessage())
            );
        }
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
