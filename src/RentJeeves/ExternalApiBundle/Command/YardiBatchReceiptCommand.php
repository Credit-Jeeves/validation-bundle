<?php

namespace RentJeeves\ExternalApiBundle\Command;

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
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->setDescription('Pushes payments to Yardi packing them into batches by batchId.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This command is not implemented yet');
    }
}
