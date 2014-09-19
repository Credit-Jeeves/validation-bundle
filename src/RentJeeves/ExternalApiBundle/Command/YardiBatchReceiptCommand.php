<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\CoreBundle\DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YardiBatchReceiptCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:yardi:push-batch-receipts')
            ->addOption(
                'date',
                null,
                InputOption::VALUE_OPTIONAL,
                'Date in format YYYY-MM-DD'
            )
            ->setDescription(
                'Pushes payments to Yardi packing them into batches by batchId.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = $input->getOption('date');
        if ($date) {
            $date = DateTime::createFromFormat('Y-m-d', $date);
        } else {
            $date = new DateTime();
        }

        $this->getContainer()
            ->get('yardi.push_batch_receipts')
            ->usingOutput($output)
            ->run($date);
    }
}
