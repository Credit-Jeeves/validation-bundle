<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\CoreBundle\DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YardiReversalReceiptCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:yardi:push-reversal-receipts')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->setDescription('Pushes reversal payments to Yardi.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $depositDate = new DateTime();
        $this->getContainer()
            ->get('yardi.push_reversal_receipts')
            ->run($depositDate);
    }
}
