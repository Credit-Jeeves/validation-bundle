<?php

namespace RentJeeves\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPropertyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:import:property')
            ->addOption('external-property-id', null, InputOption::VALUE_REQUIRED, 'External Property ID')
            ->addOption('group-id', null, InputOption::VALUE_REQUIRED, 'Group ID')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->setDescription('Import Properties by Group ID');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //@TODO add service to use
    }
}
