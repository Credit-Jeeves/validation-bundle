<?php

namespace RentJeeves\ComponentBundle\CreditSummaryReport;

use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;

abstract class BaseSummaryReportBuilder implements CreditSummaryReportBuilderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param EntityManager $em
     * @param Logger $logger
     */
    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param User $user
     * @param int $scoreValue
     * @return bool
     */
    protected function createScore(User $user, $scoreValue)
    {
        if ($scoreValue > 1000) {
            $this->logger->debug(
                sprintf(
                    '%sValue for new score "%d", so not need create new score record for user #%d',
                    static::LOGGER_PREFIX,
                    $scoreValue,
                    $user->getId()
                )
            );

            return false;
        }

        $score = new Score();
        $score->setUser($user);
        $score->setScore($scoreValue);
        $this->em->persist($score);

        $this->logger->debug(
            sprintf(
                '%sCreated new score record with value "%d" for user #%d',
                static::LOGGER_PREFIX,
                $scoreValue,
                $user->getId()
            )
        );

        return true;
    }

    /**
     * @param User $user
     * @param bool $shouldUpdateReport
     * @return Report
     * @throws \Exception
     */
    protected function getReport(User $user, $shouldUpdateReport)
    {
        if ($shouldUpdateReport) {
            $lastReportOperation = $user->getLastCompleteReportOperation();
            if (!$lastReportOperation || !($report = $lastReportOperation->getReportByVendor(static::VENDOR))) {
                $this->logger->alert(static::LOGGER_PREFIX . 'Doesn\'t have report for update');
                throw new \RuntimeException('Doesn\'t have report for update');
            }
            if ($report->getRawData()) {
                $this->logger->alert(static::LOGGER_PREFIX . 'Report have been already updated');
                throw new \RuntimeException('Report have been already updated');
            }
        } else {
            $report = $this->createNewReport($user);
        }

        return $report;
    }

    /**
     * @param User $user
     * @return Report
     */
    abstract protected function createNewReport(User $user);
}
