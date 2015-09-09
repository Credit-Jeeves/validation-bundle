<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AMSISyncRecurringChargesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('api:amsi:sync-recurring-charges')
            ->setDescription(
                'Fetch all recurring charges for checked AMSI customers and update rent for contract'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()
            ->get('amsi.contract_sync')
            ->usingOutput($output)
            ->syncRecurringCharge();
    }
}
