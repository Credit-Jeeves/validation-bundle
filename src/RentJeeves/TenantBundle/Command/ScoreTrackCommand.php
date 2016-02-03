<?php
namespace RentJeeves\TenantBundle\Command;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedCreditTrack;
use RentJeeves\DataBundle\Entity\UserSettings;
use RentJeeves\DataBundle\Enum\CreditSummaryVendor;
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
        /** @var UserSettings $userSettings */
        foreach ($usersSettings as $userSettings) {
            if ($userSettings->isScoreTrackFree()) {
                $job = new Job('score-track:get-report', ['--app=rj']);
                $report = $this->getReport();
                $report->setUser($userSettings->getUser());
                $job->addRelatedEntity($report);
                $em->persist($job);
                $reports++;
            } else {
                $job = new Job('payment:pay', ['--app=rj']);
                $relatedEntity = new JobRelatedCreditTrack();
                $relatedEntity->setCreditTrackPaymentAccount($userSettings->getCreditTrackPaymentAccount());
                $job->addRelatedEntity($relatedEntity);
                $em->persist($job);
                $paymentAccounts++;
            }
        }
        $em->flush();
        $logger->info(sprintf('%d payments added to queue', $paymentAccounts));
        $logger->info(sprintf('%d get reports added to queue', $paymentAccounts));
        $logger->info('OK');
    }

    /**
     * @return ReportPrequal|ReportTransunionSnapshot
     * @throws \Exception
     */
    protected function getReport()
    {
        $creditTrackVendor = $this->getContainer()->getParameter('credit_summary_vendor');

        CreditSummaryVendor::throwsInvalid($creditTrackVendor);

        switch ($creditTrackVendor) {
            case CreditSummaryVendor::TRANSUNION:
                return new ReportTransunionSnapshot();
            case CreditSummaryVendor::EXPERIAN:
                return new ReportPrequal();
            default:
                throw new \Exception(sprintf('Unsupported credit summary vendor "%s"', $creditTrackVendor));
        }
    }
}
