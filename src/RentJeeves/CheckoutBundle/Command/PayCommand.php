<?php
namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\DataBundle\Entity\Job;
use Doctrine\ORM\EntityManager;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Payum\Payment as Payum;
use RuntimeException;

class PayCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('payment:pay')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED, 'ID of job')
            ->setDescription('Start payment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $jobId = $input->getOption('jms-job-id');
        $this->getContainer()->get('logger')->addDebug(sprintf('Run payment:pay with job ID %s', $jobId));

        /** @var Job $job */
        $job = $em->getRepository('RjDataBundle:Job')->findOneBy(array('id' => $jobId));
        if (empty($job)) {
            throw new RuntimeException("Can not fid --jms-job-id={$jobId}");
        }

        $paymentJobExecutor = $this->getContainer()->get('checkout.payment_job_executor');
        $paymentJobExecutor->execute($job);

        $output->writeln($paymentJobExecutor->getMessage());
        return $paymentJobExecutor->getExitCode();
    }
}
