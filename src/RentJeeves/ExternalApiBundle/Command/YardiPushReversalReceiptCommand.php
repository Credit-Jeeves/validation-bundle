<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\ExternalApiBundle\Services\Yardi\ReversalReceiptSender;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YardiPushReversalReceiptCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('renttrack:yardi:push-reversal-receipt')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->addOption('order-id', null, InputOption::VALUE_REQUIRED, 'Order ID')
            ->setDescription('Pushes reversal orders to Yardi.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ReversalReceiptSender $reversalReceiptSender */
        $reversalReceiptSender = $this->getContainer()->get('yardi.reversal_receipts');
        $result = $reversalReceiptSender->pushReversedReceiptByOrderId($input->getOption('order-id'));
        if ($result) {
            $this->getContainer()->get('logger')->info(
                sprintf(
                    '[YardiPushReversalReceiptCommand] Order ID#%s pushed successfuly',
                    $input->getOption('order-id')
                )
            );
        } else {
            $this->getContainer()->get('logger')->info(
                sprintf(
                    '[YardiPushReversalReceiptCommand] Order ID#%s failed to push',
                    $input->getOption('order-id')
                )
            );
        }
    }
}
