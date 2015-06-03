<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PaymentPushCommand
 * @package RentJeeves\ExternalApiBundle\Command
 *
 * Push payment details to a landlord's external accounting software package.
 * This allows us to keep their system up-to-date in real-time.
 *
 */
class PaymentPushCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('external_api:payment:push')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job')
            ->addOption('order-id', null, InputOption::VALUE_OPTIONAL, 'ID of order')
            ->setDescription('Push transaction to external API.  You must supply a Job ID or Order ID');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws RuntimeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        if ($jobId = $input->getOption('jms-job-id')) {
            $order = $this->getOrderByJobId($em, $jobId);
        } elseif ($orderId = $input->getOption('order-id')) {
            $order = $this->getOrderById($em, $orderId);
        } else {
            throw new Exception("You must supply a Job ID or and Order ID");
        }

        /** @var AccountingPaymentSynchronizer $accountingPaymentSync */
        $accountingPaymentSync = $this->getContainer()->get('accounting.payment_sync');

        $result = $accountingPaymentSync->sendOrderToAccountingSystem($order);
        if ($result) {
            $output->writeln('Success');
        } else {
            $output->writeln('Failed');
        }
    }

    /**
     * @param $jobId
     * @param $em
     * @return \CreditJeeves\DataBundle\Entity\Order
     * @throws RuntimeException
     * @throws \Exception
     */
    protected function getOrderByJobId($em, $jobId)
    {
        $this->getContainer()->get('logger')->info(
            sprintf('Push transaction external_api:transaction:push with job ID %s', $jobId)
        );

        /** @var Job $job */
        $job = $em->getRepository('RjDataBundle:Job')->findOneBy(['id' => $jobId]);
        if (empty($job)) {
            throw new RuntimeException("Can not fid --jms-job-id={$jobId}");
        }

        $arrayCollectionJobRelatedOrder = $job->getRelatedEntities();
        if ($arrayCollectionJobRelatedOrder->count() !== 1) {
            throw new \Exception("Job should be related to exactly one order.");
        }
        /** @var JobRelatedOrder $jobRelatedOrder */
        $jobRelatedOrder = $arrayCollectionJobRelatedOrder->first();

        return $jobRelatedOrder->getOrder();
    }

    /**
     * @param $orderId
     * @param $em
     * @return \CreditJeeves\DataBundle\Entity\Order
     */
    protected function getOrderById($em, $orderId)
    {
        $this->getContainer()->get('logger')->info(
            sprintf('Push transaction external_api:transaction:push with Order ID %s', $orderId)
        );

        $orderRepository = $em->getRepository('DataBundle:Order');
        if (false == $order = $orderRepository->find($orderId)) {
            throw new RuntimeException("Can not fid --order-id={$orderId}");
        }

        return $order;
    }
}
