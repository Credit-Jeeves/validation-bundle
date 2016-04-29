<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\DataBundle\Entity\Job;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestRetryJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:test-retry')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null !== $jobId = $input->getOption('jms-job-id')) {
            throw new \Exception('Just test.');
        }

        $job = new Job($this->getName());
        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();

        $this->getLogger()->info('Job is created. Try retry this job.');
    }
}
