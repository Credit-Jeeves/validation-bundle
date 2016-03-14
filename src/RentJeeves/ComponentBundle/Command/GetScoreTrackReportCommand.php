<?php
namespace RentJeeves\ComponentBundle\Command;

use RentJeeves\ComponentBundle\CreditSummaryReport\CreditSummaryReportBuilderInterface;
use RentJeeves\CoreBundle\Command\BaseCommand;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedReport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetScoreTrackReportCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('score-track:get-report')
            ->setDescription('Getting report from default vendor')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED, 'ID of job');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start');
        $this->getLogger()->debug('[Get ScoreTrack Report Command]Start load report');
        $jobId = $input->getOption('jms-job-id');

        /** @var Job $job */
        $job = $this->getEntityManager()->getRepository('RjDataBundle:Job')->find($jobId);
        if (empty($job)) {
            $this->getLogger()->debug(
                '[Get ScoreTrack Report Command]' . sprintf('Can not find --jms-job-id=%s', $jobId)
            );
            throw new \RuntimeException(sprintf('Can not find --jms-job-id=%s', $jobId));
        }

        foreach ($job->getRelatedEntities() as $relatedEntity) {
            if ($relatedEntity instanceof JobRelatedReport) {
                $report = $relatedEntity->getReport();
                if ($report->getRawData()) {
                    $output->writeln('Report already received');
                    $this->getLogger()->debug('[Get ScoreTrack Report Command]Report already received');

                    return 0;
                }

                try {
                    $this->getReportBuilder()->build($report->getUser(), $updateExistReport = true);
                } catch (\Exception $e) {
                    $this->getLogger()->alert('[Get ScoreTrack Report Command]Failed: ' . $e->getMessage());
                    $output->writeln($e->getMessage());

                    return 1;
                }
                $output->writeln('OK');
                $this->getLogger()->debug('[Get ScoreTrack Report Command]Report load successfuly');

                if ($report->getUser()->getSettings()->isScoreTrackFree()) {
                    $this->getContainer()->get('project.mailer')->sendFreeReportUpdated($report->getUser());
                }

                return 0;
            }
        }

        $output->writeln('Job does not have related report record');
        $this->getLogger()->alert(
            sprintf('[Get ScoreTrack Report Command]Job #%d does not have related report record', $jobId)
        );

        return 1;
    }

    /**
     * @return CreditSummaryReportBuilderInterface
     */
    protected function getReportBuilder()
    {
        return $this->getContainer()->get('credit_summary.report_builder_factory')->getReportBuilder();
    }
}
