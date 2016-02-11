<?php

namespace RentJeeves\ComponentBundle\CreditSummaryReport;

use CreditJeeves\CoreBundle\Enum\ScoreModelType;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\ExperianBundle\NetConnect\CreditProfile;
use RentJeeves\DataBundle\Enum\CreditSummaryVendor;

class ExperianReportBuilder extends BaseSummaryReportBuilder
{
    const LOGGER_PREFIX = '[Experian Report Builder]';

    const VENDOR = CreditSummaryVendor::EXPERIAN;
    /**
     * @var CreditProfile
     */
    protected $creditProfile;

    /**
     * @param CreditProfile $creditProfile
     */
    public function setCreditProfile(CreditProfile $creditProfile)
    {
        $this->creditProfile = $creditProfile;
    }

    /**
     * @TODO add support type of reports
     * {@inheritdoc}
     */
    public function build(User $user, $shouldUpdateReport = false)
    {
        $this->logger->debug(
            static::LOGGER_PREFIX . 'Try build credit summary report for user #' . $user->getId()
        );

        $report = $this->getReport($user, $shouldUpdateReport);

        $reportData = $this->creditProfile->getResponseOnUserData($user);

        $this->logger->debug(
            static::LOGGER_PREFIX . 'Got report data from experian for user #' . $user->getId()
        );

        $report->setRawData($reportData);
        $this->em->persist($report);

        $newScore = $report->getArfReport()->getScore(ScoreModelType::VANTAGE3);
        $this->createScore($user, $newScore);

        $this->em->flush();
    }

    /**
     * @param User $user
     * @return ReportPrequal
     */
    public function createNewReport(User $user)
    {
        $report = new ReportPrequal();
        $report->setUser($user);

        return $report;
    }
}
