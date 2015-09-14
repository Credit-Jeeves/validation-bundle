<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Monolog\Logger;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\Serializer\Normalizer\HPSDepositReportDenormalizer;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\Serializer\Encoder\CsvFileDecoder;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\Serializer\Normalizer\HPSReversalReportDenormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @DI\Service("payment_processor.heartland.report_loader")
 */
class ReportLoader
{
    const DEPOSIT_REPORT_FILENAME_SUFFIX = 'ACHDepositsandChargesExport';
    const REVERSAL_REPORT_FILENAME_SUFFIX = 'BillDataExport';

    /** @var EntityManagerInterface */
    protected $em;

    /** @var HpsReportFinder */
    protected $fileFinder;

    /** @var HpsReportArchiver */
    protected $fileArchiver;

    /** @var Logger */
    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "fileFinder" = @DI\Inject("payment_processor.hps_report.finder"),
     *     "fileArchiver" = @DI\Inject("payment_processor.hps_report.archiver"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(
        EntityManagerInterface $em,
        HpsReportFinder $fileFinder,
        HpsReportArchiver $fileArchiver,
        Logger $logger
    ) {
        $this->em = $em;
        $this->fileFinder = $fileFinder;
        $this->fileArchiver = $fileArchiver;
        $this->logger = $logger;
    }

    /**
     * Loads deposits and reversals from Heartland report.
     *
     * @return PaymentProcessorReport
     */
    public function loadReport()
    {
        $this->logger->debug('HPS: Trying to load report');

        $depositReport = $this->loadDepositReport();
        $depositReportBackwards = array_reverse($depositReport);
        unset($depositReport);

        $reversalReport = $this->loadReversalReport();
        $reversalReportBackwards = array_reverse($reversalReport);
        unset($reversalReport);

        $reportTransactions = array_merge($depositReportBackwards, $reversalReportBackwards);
        unset($depositReportBackwards);
        unset($reversalReportBackwards);

        $report = new PaymentProcessorReport();
        $report->setTransactions($reportTransactions);

        return $report;
    }

    /**
     * @return array
     */
    protected function loadDepositReport()
    {
        if (!$reportFiles = $this->findReports(self::DEPOSIT_REPORT_FILENAME_SUFFIX)) {
            $this->logger->emergency('HPS: deposit report not found');

            return [];
        }

        $csvDecoder = new CsvFileDecoder();
        $denormalizer = new HPSDepositReportDenormalizer();
        $serializer = new Serializer([$denormalizer], [$csvDecoder]);

        $reportTransactions = [];
        foreach ($reportFiles as $reportFilename) {
            $this->logger->debug('HPS: loading deposit report ' . $reportFilename);

            $reportTransactions += $serializer->deserialize(
                $reportFilename,
                'RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction',
                HPSDepositReportDenormalizer::FORMAT
            );

            $this->logger->debug('HPS: archiving deposit report ' . $reportFilename);
            $this->archiveReport($reportFilename, self::DEPOSIT_REPORT_FILENAME_SUFFIX);
        }

        return $reportTransactions;
    }

    /**
     * @return array
     */
    protected function loadReversalReport()
    {
        if (!$reportFiles = $this->findReports(self::REVERSAL_REPORT_FILENAME_SUFFIX)) {
            $this->logger->emergency('HPS: reversal report not found');

            return [];
        }

        $reportTransactions = [];
        foreach ($reportFiles as $reportFilename) {
            $this->logger->debug('HPS: loading reversal report ' . $reportFilename);

            $csvDecoder = new CsvFileDecoder();
            $denormalizer = new HPSReversalReportDenormalizer();

            $serializer = new Serializer([$denormalizer], [$csvDecoder]);

            $reportTransactions += $serializer->deserialize(
                $reportFilename,
                'RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction',
                HPSReversalReportDenormalizer::FORMAT
            );

            $this->logger->debug('HPS: archiving reversal report ' . $reportFilename);
            $this->archiveReport($reportFilename, self::REVERSAL_REPORT_FILENAME_SUFFIX);
        }

        return $reportTransactions;
    }

    /**
     * @param string $fileSuffix
     * @return array
     */
    protected function findReports($fileSuffix)
    {
        return $this->fileFinder->find($fileSuffix);
    }

    /**
     * @param string $file
     * @param string $fileSuffix
     * @return bool
     */
    protected function archiveReport($file, $fileSuffix)
    {
        return $this->fileArchiver->archive($file, $fileSuffix);
    }
}
