<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AMSISyncBalanceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:amsi:sync-balance')
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
            ->get('amsi.contract_sync')
            ->usingOutput($output)
            ->syncBalance();
    }
}
