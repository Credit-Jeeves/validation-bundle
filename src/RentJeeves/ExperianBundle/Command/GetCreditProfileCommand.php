<?php
namespace RentJeeves\ExperianBundle\Command;

use RentJeeves\DataBundle\Entity\Job;
use Doctrine\ORM\EntityManager;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\JobRelatedReport;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Payum\Payment as Payum;
use RuntimeException;

class GetCreditProfileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('experian-credit_profile:get')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED, 'ID of job')
            ->setDescription('Getting credit profile');
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

        foreach ($job->getRelatedEntities() as $relatedEntity) {
            if ($relatedEntity instanceof JobRelatedReport) {
                $report = $relatedEntity->getReport();
                if ('' != $report->getRawData()) {
                    $output->writeln('Report already received');
                    return 0;
                }
                $report->setRawData(
                    $this->getContainer()->get('experian.net_connect.credit_profile')->initD2c()
                        ->getResponseOnUserData($report->getUser())
                );
                $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
                $em->persist($report);
                $em->flush();

                $output->writeln('OK');
                return 0;
            }
        }


        $output->writeln('Job does not have related report record');
        return 1;
    }
}
