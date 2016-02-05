<?php

namespace RentJeeves\CoreBundle\Report;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\Equifax\EquifaxClosureReport;
use RentJeeves\CoreBundle\Report\Equifax\EquifaxPositiveReport;
use RentJeeves\CoreBundle\Report\Equifax\EquifaxRentalReport;
use RentJeeves\CoreBundle\Report\Experian\ExperianClosureReport;
use RentJeeves\CoreBundle\Report\Experian\ExperianPositiveReport;
use RentJeeves\CoreBundle\Report\Experian\ExperianRentalReport;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionClosureReport;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionNegativeReport;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionPositiveReport;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionRentalReport;

class RentalReportFactory
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $propertyManagement;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, array $propertyManagement)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->propertyManagement = $propertyManagement;
    }

    /**
     * @param RentalReportData $data
     * @throws \RuntimeException
     * @return RentalReport
     */
    public function getReport(RentalReportData $data)
    {
        switch ($data->getBureau()) {
            case CreditBureau::TRANS_UNION:
                $report = self::getTransUnionReport($data->getType());
                break;
            case CreditBureau::EXPERIAN:
                $report = self::getExperianReport($data->getType());
                break;
            case CreditBureau::EQUIFAX:
                $report = self::getEquifaxReport($data->getType());
                break;
            default:
                throw new \RuntimeException(sprintf('Given report bureau \'%s\' does not exist', $data->getBureau()));
        }

        return $report;
    }

    /**
     * @param string $type
     * @return TransUnionRentalReport
     */
    protected function getTransUnionReport($type)
    {
        switch ($type) {
            case RentalReportType::POSITIVE:
                $report = new TransUnionPositiveReport($this->em, $this->logger, $this->propertyManagement);
                break;
            case RentalReportType::NEGATIVE:
                $report = new TransUnionNegativeReport($this->em, $this->logger, $this->propertyManagement);
                break;
            case RentalReportType::CLOSURE:
                $report = new TransUnionClosureReport($this->em, $this->logger, $this->propertyManagement);
                break;
            default:
                throw new \RuntimeException(sprintf('TransUnion report type \'%s\' does not exist', $type));
        }

        return $report;
    }

    /**
     * @param string $type
     * @return ExperianRentalReport
     */
    protected function getExperianReport($type)
    {
        switch ($type) {
            case RentalReportType::POSITIVE:
                $report = new ExperianPositiveReport($this->em, $this->logger);
                break;
            case RentalReportType::CLOSURE:
                $report = new ExperianClosureReport($this->em, $this->logger);
                break;
            default:
                throw new \RuntimeException(sprintf('Experian report type \'%s\' does not exist', $type));
        }

        return $report;
    }

    /**
     * @param string $type
     * @return EquifaxRentalReport
     */
    protected function getEquifaxReport($type)
    {
        switch ($type) {
            case RentalReportType::POSITIVE:
                $report = new EquifaxPositiveReport($this->em, $this->logger, $this->propertyManagement);
                break;
            case RentalReportType::CLOSURE:
                $report = new EquifaxClosureReport($this->em, $this->logger, $this->propertyManagement);
                break;
            default:
                throw new \RuntimeException(sprintf('Equifax report type \'%s\' does not exist', $type));
        }

        return $report;
    }
}
