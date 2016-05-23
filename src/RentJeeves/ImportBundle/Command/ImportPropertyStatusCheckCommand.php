<?php

namespace RentJeeves\ImportBundle\Command;

use JMS\JobQueueBundle\Entity\Job;
use RentJeeves\CoreBundle\Command\BaseCommand;
use RentJeeves\DataBundle\Enum\ImportStatus;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPropertyStatusCheckCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:import:property:check-status')
            ->addOption('import-id', null, InputOption::VALUE_REQUIRED, 'Import ID')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->setDescription('Check status for Import of Group.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->info('Check Import Status.');

        $importId = $input->getOption('import-id');
        if (null == $import = $this->getEntityManager()->getRepository('RjDataBundle:Import')->find($importId)) {
            throw new \InvalidArgumentException(sprintf('Entity Import#%s not found', $importId));
        }

        $notFinishedJobs = $this->getEntityManager()->createQueryBuilder()
            ->select('j')
            ->from('RjDataBundle:Job', 'j')
            ->where('j.state NOT IN (:finishedStatus)')
            ->andWhere('j.command = :command')
            ->andWhere('j.args LIKE :arg')
            ->setParameter('finishedStatus', [Job::STATE_FAILED, Job::STATE_FINISHED])
            ->setParameter('arg', '%--import-id=' . $importId . '%')
            ->setParameter('command', 'renttrack:import:property')
            ->getQuery()
            ->execute();

        if (true === empty($notFinishedJobs) && $import->getStatus() !== ImportStatus::ERROR) {
            $import->setStatus(ImportStatus::COMPLETE);
            $import->setFinishedAt(new \DateTime());
            $this->getEntityManager()->flush();
            $this->getLogger()->info('Status is updated.');
        }
    }
}
