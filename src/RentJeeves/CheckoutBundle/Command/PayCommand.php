<?php
namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\PayRent;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedPayment;
use Doctrine\ORM\EntityManager;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Payum\Payment as Payum;
use DateTime;
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

        /** @var Job $job */
        $job = $em->getRepository('RjDataBundle:Job')->findOneBy(array('id' => $jobId));
        if (empty($job)) {
            throw new RuntimeException("Can not fid --jms-job-id={$jobId}");
        }

        $date = new DateTime();

        /** @var JobRelatedPayment $relatedPayment */
        $relatedPayment = $job->findRelatedEntity('RentJeeves\DataBundle\Entity\JobRelatedPayment');

        if (empty($relatedPayment)) {
            throw new RuntimeException("Job ID:'{$jobId}' must have related payment");
        }
        $payment = $relatedPayment->getPayment();
        $contract = $payment->getContract();

        $filterClosure = function (Operation $operation) use ($date) {
            if (($order = $operation->getOrder()) &&
                $order->getCreatedAt()->format('Y-m-d') == $date->format('Y-m-d') &&
                OrderStatus::ERROR != $order->getStatus()
            ) {
                return true;
            }
            return false;
        };
        if ($contract->getOperations()->filter($filterClosure)->count()) {
            $output->writeln('Payment already executed.');
            return 1;
        }

        /** @var PayRent $payRent */
        $payRent = $this->getContainer()->get('payment.pay_rent');
        $job->addRelatedEntity($payRent->getOrder());
        $em->persist($job);
        $statusRequest = $payRent->executePayment($payment);

        if (!$statusRequest->isSuccess()) {
            $output->writeln($statusRequest->getModel()->getMessages());
            return 1;
        }
        $output->writeln('OK');
    }
}
