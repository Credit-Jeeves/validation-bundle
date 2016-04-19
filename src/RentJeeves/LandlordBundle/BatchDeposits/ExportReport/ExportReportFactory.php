<?php
namespace RentJeeves\LandlordBundle\BatchDeposits\ExportReport;

use CreditJeeves\DataBundle\Entity\Holding;
use Psr\Log\LoggerInterface;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;

class ExportReportFactory
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @var array Assoc array, where
     *  key = AccountingSystem`s name
     *  value = service which extends ExportReport
     */
    protected $supportedExportReports;

    /**
     * ExportReportFactory constructor.
     * @param LoggerInterface $logger
     * @param array $supportedExportReports
     */
    public function __construct(LoggerInterface $logger, array $supportedExportReports)
    {
        $this->logger = $logger;
        $this->supportedExportReports = $supportedExportReports;
    }

    /**
     * @param Holding $holding
     * @return null|ExportReport
     */
    public function getExportReport(Holding $holding)
    {
        $accountingSystemName = $holding->getAccountingSystem();

        if (false === in_array($accountingSystemName, array_keys($this->supportedExportReports))) {
            $this->logger->info(
                sprintf(
                    'ExportReportFactory: Holding #%s has account system %s. Unsupported export report',
                    $holding->getId(),
                    $holding->getAccountingSystem()
                )
            );

            return null;
        }

        return $this->supportedExportReports[$accountingSystemName];
    }
}
