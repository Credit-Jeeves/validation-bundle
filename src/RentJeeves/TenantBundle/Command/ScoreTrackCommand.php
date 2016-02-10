<?php
namespace RentJeeves\TenantBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedCreditTrack;
use RentJeeves\DataBundle\Entity\UserSettings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScoreTrackCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('score-track:collect-payments')
            ->setDescription('Start collect Score Track payments')
            ->setHelp('This command must be run only once par day!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $logger->info('Start:');
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $usersSettings = $em->getRepository('RjDataBundle:UserSettings')
            ->getUserSettingsForCreditTrackByTodayDueDay();

        $paymentAccounts = 0;
        $reports = 0;

        $reportBuilder = $this->getContainer()->get('credit_summary.report_builder_factory')
            ->getReportBuilder($this->getContainer()->getParameter('credit_summary_vendor'));

        /** @var UserSettings $userSettings */
        foreach ($usersSettings as $userSettings) {
            if ($userSettings->isScoreTrackFree()) {
                $report = $reportBuilder->createNewReport($userSettings->getUser());
                $job = new Job('score-track:get-report');
                $job->addRelatedEntity($report);
                $em->persist($report);
                $reports++;
            } else {
                $job = new Job('payment:pay');
                $relatedEntity = new JobRelatedCreditTrack();
                $relatedEntity->setCreditTrackPaymentAccount($userSettings->getCreditTrackPaymentAccount());
                $job->addRelatedEntity($relatedEntity);
                $paymentAccounts++;
            }

            $em->persist($job);
        }
        $em->flush();
        $logger->info(sprintf('%d payments added to queue', $paymentAccounts));
        $logger->info(sprintf('%d get reports added to queue', $reports));
    }
}
