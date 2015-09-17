<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class YardiSyncRentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:yardi:sync-rent')
            ->setDescription('Update resident rent for Yardi.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()
            ->get('yardi.contract_sync')
            ->syncRecurringCharge();
    }
}
