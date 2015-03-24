<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Monolog\Logger;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorReportType;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReport;
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

    /** @var Logger */
    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "fileFinder" = @DI\Inject("payment.report.finder"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(EntityManagerInterface $em, HpsReportFinder $fileFinder, Logger $logger)
    {
        $this->em = $em;
        $this->fileFinder = $fileFinder;
        $this->logger = $logger;
    }

    /**
     * Loads report by report type.
     *
     * @param string $reportType
     * @return DepositReport | ReversalReport
     * @throws \Exception
     */
    public function loadReport($reportType, array $settings)
    {
        $makeArchive = isset($settings['make_archive'])? $settings['make_archive'] : false;
        $this->logger->debug('HPS: Trying to load report of type ' . $reportType);
        switch ($reportType) {
            case PaymentProcessorReportType::DEPOSIT:
                return $this->loadDepositReport($makeArchive);
            case PaymentProcessorReportType::REVERSAL:
                return $this->loadReversalReport($makeArchive);
            default:
                throw new \Exception('HPS: Unexpected report type.');
        }
    }

    /**
     * @param bool $makeArchive
     * @return DepositReport
     */
    protected function loadDepositReport($makeArchive)
    {
        if (!$filename = $this->findReportFile(self::DEPOSIT_REPORT_FILENAME_SUFFIX)) {
            $this->logger->debug('HPS: deposit report not found');
            return null;
        }

        $this->logger->debug('HPS: loading deposit report ' . $filename);

        $csvDecoder = new CsvFileDecoder();
        $denormalizer = new HPSDepositReportDenormalizer();

        $serializer = new Serializer([$denormalizer], [$csvDecoder]);

        /** @var DepositReport $report */
        $report = $serializer->deserialize(
            $filename,
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReport',
            'hps_csv_file'
        );

        // Archive loaded report
        if ($makeArchive) {
            $this->logger->debug('HPS: archiving deposit report ' . $filename);
            $this->archiveReportFile($filename, self::DEPOSIT_REPORT_FILENAME_SUFFIX);
        }

        return $report;
    }

    /**
     * @param bool $makeArchive
     * @return ReversalReport
     */
    protected function loadReversalReport($makeArchive)
    {
        if (!$filename = $this->findReportFile(self::REVERSAL_REPORT_FILENAME_SUFFIX)) {
            $this->logger->debug('HPS: reversal report not found');
            return null;
        }

        $this->logger->debug('HPS: loading reversal report ' . $filename);

        $csvDecoder = new CsvFileDecoder();
        $denormalizer = new HPSReversalReportDenormalizer();

        $serializer = new Serializer([$denormalizer], [$csvDecoder]);

        /** @var ReversalReport $report */
        $report = $serializer->deserialize(
            $filename,
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReport',
            'hps_csv_file'
        );

        // Archive loaded report
        if ($makeArchive) {
            $this->logger->debug('HPS: archiving reversal report ' . $filename);
            $this->archiveReportFile($filename, self::REVERSAL_REPORT_FILENAME_SUFFIX);
        }

        return $report;
    }

    /**
     * @param string $fileSuffix
     * @return null|string
     */
    protected function findReportFile($fileSuffix)
    {
        return $this->fileFinder->findBySuffix($fileSuffix);
    }

    /**
     * @param string $file
     * @param string $fileSuffix
     * @return bool
     */
    protected function archiveReportFile($file, $fileSuffix)
    {
        return $this->fileFinder->archive($file, $fileSuffix);
    }
}
