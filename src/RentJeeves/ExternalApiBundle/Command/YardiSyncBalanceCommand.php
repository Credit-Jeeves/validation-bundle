<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YardiSyncBalanceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:yardi:sync-balance')
            ->setDescription('Update resident balances for Yardi.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()
            ->get('yardi.resident_balance_sync')
            ->run();
    }
}
