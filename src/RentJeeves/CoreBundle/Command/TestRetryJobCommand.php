<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\DataBundle\Entity\Job;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class TestRetryJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:test-retry')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job')
            ->addOption('pass-after-time', null, InputOption::VALUE_OPTIONAL, 'fail job until this epoch timestamp');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null !== $jobId = $input->getOption('jms-job-id')) {
            $failUntilTime = $input->getOption('pass-after-time');
            $this->getLogger()->info('We are a job! Fail:' . $failUntilTime);
            $expired = time() - $failUntilTime;

            if ($expired <= 6) {
                sleep(4);
                $output->writeln(sprintf('Failed (expired: %s seconds).', $expired));

                return 1;
            }

            $output->writeln('Success.');

            return 0;
        }

        $failUntilTime = time() + 12;
        $this->getLogger()->info(' We should create the job! Time:' . $failUntilTime);
        $job = new Job($this->getName(), ['--pass-after-time='.$failUntilTime]);
        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();

        $this->getLogger()->info('Job is created. Try retry this job.');
    }
}
