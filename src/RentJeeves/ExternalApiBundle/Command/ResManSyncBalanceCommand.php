<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResManSyncBalanceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:resman:sync-balance')
            ->setDescription('Update resident balances.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()
            ->get('resman.contract_sync')
            ->usingOutput($output)
            ->syncBalance();
    }
}
