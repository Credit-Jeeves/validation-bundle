<?php

namespace RentJeeves\CoreBundle\Report;

use Doctrine\ORM\EntityManagerInterface;
use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionClosureReport;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionPositiveReport;
use RentJeeves\CoreBundle\Report\TransUnion\TransUnionRentalReport;

class RentalReportFactory
{
    /**
     * @param RentalReportData $data
     * @param EntityManagerInterface $em
     * @param array $propertyManagement
     * @return RentalReport
     */
    public static function getReport(RentalReportData $data, EntityManagerInterface $em, array $propertyManagement)
    {
        switch ($data->getBureau()) {
            case CreditBureau::TRANS_UNION:
                $report = self::getTransUnionReport($data->getType(), $em, $propertyManagement);
                break;
            case CreditBureau::EXPERIAN:
                $report = new ExperianRentalReport($em, $type);
                break;
            default:
                throw new \RuntimeException(sprintf('Given report bureau "\'%s\'" does not exist', $data->getBureau()));
        }

        return $report;
    }

    /**
     * @param string $type
     * @param EntityManagerInterface $em
     * @param array $propertyManagement
     * @return TransUnionRentalReport
     */
    public static function getTransUnionReport($type, EntityManagerInterface $em, array $propertyManagement)
    {
        switch ($type) {
            case RentalReportType::POSITIVE:
                $report = new TransUnionPositiveReport($em, $propertyManagement);
                break;
            case RentalReportType::NEGATIVE:
                break;
            case RentalReportType::CLOSURE:
                $report = new TransUnionClosureReport($em, $propertyManagement);
                break;
            default:
                throw new \RuntimeException(sprintf('TransUnion report type "\'%s\'" does not exist', $type));
        }

        return $report;
    }
}
