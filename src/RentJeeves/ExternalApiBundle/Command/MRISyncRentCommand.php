<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MRISyncRentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:mri:sync-rent')
            ->setDescription('Update resident rent.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()
            ->get('mri.contract_sync')
            ->syncRecurringCharge();
    }
}
