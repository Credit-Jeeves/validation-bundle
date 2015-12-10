<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\ReversalReceiptSender;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YardiReversalReceiptCollectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:yardi:collect-reversal-receipts')
            ->setDescription('Collect reversal payments to Yardi.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ReversalReceiptSender $reversalReceiptSender */
        $reversalReceiptSender = $this->getContainer()->get('yardi.reversal_receipts');
        $result = $reversalReceiptSender->сollectReversalPaymentsToJobsForDate(new DateTime());
        if ($result) {
            $this->getContainer()->get('logger')->debug('[YardiPushReversalReceiptCommand] Collected successfully');
        } else {
            $this->getContainer()->get('logger')->debug('[YardiPushReversalReceiptCommand] Failed collect');
        }
    }
}
