<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentPushCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('external_api:payment:push')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED, 'ID of job')
            ->setDescription('Push transaction to external API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $jobId = $input->getOption('jms-job-id');
        $this->getContainer()->get('logger')->debug(
            sprintf('Push transaction external_api:transaction:push with job ID %s', $jobId)
        );

        /** @var Job $job */
        $job = $em->getRepository('RjDataBundle:Job')->findOneBy(['id' => $jobId]);
        if (empty($job)) {
            throw new RuntimeException("Can not fid --jms-job-id={$jobId}");
        }

        /** @var AccountingPaymentSynchronizer $accountingPaymentSync */
        $accountingPaymentSync = $this->getContainer()->get('accounting.payment_sync');
        $arrayCollectionJobRelatedOrder = $job->getRelatedEntities();
        if ($arrayCollectionJobRelatedOrder->count() !== 1) {
            throw new \Exception("Job should be related to exactly one order.");
        }
        /** @var JobRelatedOrder $jobRelatedOrder */
        $jobRelatedOrder = $arrayCollectionJobRelatedOrder->first();
        $result = $accountingPaymentSync->sendOrderToAccountingSystem($jobRelatedOrder->getOrder());
        if ($result) {
            $output->writeln('Success');
        } else {
            $output->writeln('Failed');
        }
    }
}
