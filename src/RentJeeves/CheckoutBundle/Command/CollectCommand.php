<?php
namespace RentJeeves\CheckoutBundle\Command;

use JMS\JobQueueBundle\Entity\Job;
use Doctrine\ORM\EntityManager;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\CoreBundle\Traits\DateCommon;
use Payum\Payment as Payum;
use \DateTime;

class CollectCommand extends ContainerAwareCommand
{
    use DateCommon;

    protected function configure()
    {
        $this
            ->setName('payment:collect')
            ->setDescription('Start collect payments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new DateTime();
        $days = $this->getDueDays();
        /** @var PaymentRepository $repo */
        $doctrine = $this->getContainer()->get('doctrine');
        $payments = $doctrine->getRepository('RjDataBundle:Payment')
            ->getActivePayments(
                $days,
                $date->format('n'),
                $date->format('Y')
            );
        $output->write('Start:');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        foreach ($payments as $row) {
            /** @var Payment $payment */
            $payment = $row[0];
            $job = new Job('payment:pay', array('--app=rj'));
            $job->addRelatedEntity($payment);
            $em->persist($job);
            $em->flush();
            $em->clear();
            $output->write('.');
        }
        $output->writeln('OK');
    }
}
