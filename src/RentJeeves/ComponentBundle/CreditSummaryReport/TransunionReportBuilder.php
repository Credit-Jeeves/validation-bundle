<?php

namespace RentJeeves\ComponentBundle\CreditSummaryReport;

use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use RentJeeves\ExternalApiBundle\Services\Transunion\TransUnionUserCreatorTrait;
use CreditJeeves\DataBundle\Entity\User;
use RentTrack\TransUnionBundle\CCS\Services\CreditSnapshot;
use RentTrack\TransUnionBundle\CCS\Services\VantageScore3;

class TransunionReportBuilder extends BaseSummaryReportBuilder
{
    const LOGGER_PREFIX = '[Transunion Report Builder]';
    use TransUnionUserCreatorTrait;

    /**
     * @var CreditSnapshot
     */
    protected $snapshotCreator;

    /**
     * @var array
     */
    protected $snapshotCreatorConfig;

    /**
     * @var VantageScore3
     */
    protected $scoreReceiver;

    /**
     * @var array
     */
    protected $scoreReceiverConfig;

    /**
     * @param CreditSnapshot $snapshotCreator
     * @param array $snapshotCreatorConfig
     */
    public function setSnapshotCreator(CreditSnapshot $snapshotCreator, $snapshotCreatorConfig)
    {
        $this->snapshotCreator = $snapshotCreator;
        $this->snapshotCreatorConfig = $snapshotCreatorConfig;
    }

    /**
     * @param VantageScore3 $scoreReceiver
     * @param array $scoreReceiverConfig
     */
    public function setScoreReceiver(VantageScore3 $scoreReceiver, $scoreReceiverConfig)
    {
        $this->scoreReceiver = $scoreReceiver;
        $this->scoreReceiverConfig = $scoreReceiverConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function build(User $user, $shouldUpdateReport = false)
    {
        $this->logger->debug(
            static::LOGGER_PREFIX . 'Try build credit summary report for user #' . $user->getId()
        );

        if ($shouldUpdateReport) {
            $lastReportOperation = $user->getLastCompleteReportOperation();
            if (!$lastReportOperation || !$lastReportOperation->getReportTransunionSnapshot()) {
                $this->logger->alert(static::LOGGER_PREFIX . 'Doesn\'t have report for update');
                throw new \RuntimeException('Doesn\'t have report for update');
            }
            $report = $lastReportOperation->getReportTransunionSnapshot();
        } else {
            $report = new ReportTransunionSnapshot();
            $report->setUser($user);
        }

        $transUnionUser = $this->getTransUnionUser($user);
        $snapshot = $this->snapshotCreator->getSnapshot($transUnionUser, $this->snapshotCreatorConfig);

        $this->logger->debug(
            static::LOGGER_PREFIX . 'Got snapshot from transunion for user #' . $user->getId()
        );

        $report->setRawData($snapshot);
        $this->em->persist($report);

        $newScore = $this->scoreReceiver->getScore($transUnionUser, $this->scoreReceiverConfig);
        $this->createScore($user, $newScore);

        $this->em->flush();
    }
}
