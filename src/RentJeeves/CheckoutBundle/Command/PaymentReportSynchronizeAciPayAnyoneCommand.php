<?php

namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentReportSynchronizeAciPayAnyoneCommand extends BaseCommand
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
        try {
            $paymentProcessor = $this->getAciPayAnyonePaymentProcessor();
            $report = $paymentProcessor->loadReport();
            $result = $this->getReportSynchronizer()->synchronize($report, $paymentProcessor, false);
            $output->writeln(sprintf('Amount of synchronized payments: %s', $result));
        } catch (\Exception $e) {
            $this->getLogger()->emergency(
                sprintf('PaymentReportSynchronizeAciPayAnyoneCommand finished with error : %s', $e->getMessage())
            );
        }
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
