<?php

namespace RentJeeves\ComponentBundle\CreditSummaryReport;

use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use RentJeeves\DataBundle\Enum\CreditSummaryVendor;
use CreditJeeves\DataBundle\Entity\User;
use RentTrack\TransUnionBundle\CCS\Model\TransUnionUser;
use RentTrack\TransUnionBundle\CCS\Services\CreditSnapshot;
use RentTrack\TransUnionBundle\CCS\Services\VantageScore3;

class TransunionReportBuilder extends BaseSummaryReportBuilder
{
    const LOGGER_PREFIX = '[Transunion Report Builder]';

    const VENDOR = CreditSummaryVendor::TRANSUNION;

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

        $report = $this->getReport($user, $shouldUpdateReport);

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

    /**
     * @param User $user
     * @return ReportTransunionSnapshot
     */
    protected function createNewReport(User $user)
    {
        $report = new ReportTransunionSnapshot();
        $report->setUser($user);

        return $report;
    }

    /**
     * @param User $user
     * @return TransUnionUser
     */
    protected function getTransUnionUser(User $user)
    {
        $address = $user->getDefaultAddress();

        $tuUser = new TransUnionUser();
        $tuUser
            ->setClientId($user->getId())
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setDateOfBirth($user->getDateOfBirth()->format('Y-m-d'))
            ->setSsn($user->getSsn())
            ->setStreet($address->getAddress())
            ->setCity($address->getCity())
            ->setState($address->getArea())
            ->setZipCode($address->getZip());

        return $tuUser;
    }
}
