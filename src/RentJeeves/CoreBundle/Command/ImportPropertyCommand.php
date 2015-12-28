<?php

namespace RentJeeves\CoreBundle\Command;

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
            ->addOption('group-id', null, InputOption::VALUE_REQUIRED, 'Group ID')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->setDescription('Import Properties by Group ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
