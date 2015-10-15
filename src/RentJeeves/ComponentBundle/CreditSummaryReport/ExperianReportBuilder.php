<?php

namespace RentJeeves\ComponentBundle\CreditSummaryReport;

use CreditJeeves\CoreBundle\Enum\ScoreModelType;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\ExperianBundle\NetConnect\CreditProfile;

class ExperianReportBuilder extends BaseSummaryReportBuilder
{
    const LOGGER_PREFIX = '[Experian Report Builder]';
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

        if ($shouldUpdateReport) {
            $lastReportOperation = $user->getLastCompleteReportOperation();
            if (!$lastReportOperation || !$lastReportOperation->getReportPrequal()) {
                $this->logger->alert(static::LOGGER_PREFIX . 'Doesn\'t have report for update');
                throw new \RuntimeException('Doesn\'t have report for update');
            }
            $report = $lastReportOperation->getReportPrequal();
        } else {
            $report = new ReportPrequal();
            $report->setUser($user);
        }

        $reportData = $this->getArf($user);

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
     * @return array
     */
    protected function getArf(User $user)
    {
        return $this->creditProfile->getResponseOnUserData($user);
    }
}
