<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BatchCloseFailureNotifierCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:notify:batch-close-failure')
            ->addOption(
                'jms-job-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Job ID'
            )
            ->addOption(
                'holding-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Holding id in renttrack system'
            )
            ->addOption(
                'accounting-batch-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Accounting batch id exist for some accounting system'
            )
            ->setDescription(
                'Fetch all failure order by holding and send email to landlord about this failure'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $holding = $em->getRepository('DataBundle:Holding')->find($input->getArgument('holding-id'));

        $this->getContainer()
            ->get('batch.close.failure.notifier')
            ->notify($holding, $input->getArgument('accounting-batch-id'));
    }
}

