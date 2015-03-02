<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use Doctrine\ORM\EntityManagerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorReportType;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Serializer\Normalizer\HPSDepositReportDenormalizer;
use RentJeeves\CheckoutBundle\PaymentProcessor\Serializer\Encoder\CsvFileDecoder;
use RentJeeves\CheckoutBundle\PaymentProcessor\Serializer\Normalizer\HPSReversalReportDenormalizer;
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

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "fileFinder" = @DI\Inject("payment.report.finder")
     * })
     */
    public function __construct(EntityManagerInterface $em, HpsReportFinder $fileFinder)
    {
        $this->em = $em;
        $this->fileFinder = $fileFinder;
    }

    /**
     * Loads report by report type.
     *
     * @param $reportType
     * @return DepositReport | ReversalReport
     * @throws \Exception
     */
    public function loadReport($reportType, $makeArchive = false)
    {
        switch ($reportType) {
            case PaymentProcessorReportType::DEPOSIT:
                return $this->loadDepositReport($makeArchive);
            case PaymentProcessorReportType::REVERSALS:
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
        if (!$file = $this->findReportFile(self::DEPOSIT_REPORT_FILENAME_SUFFIX)) {
            return null;
        }

        $csvDecoder = new CsvFileDecoder();
        $denormalizer = new HPSDepositReportDenormalizer();

        $serializer = new Serializer([$denormalizer], [$csvDecoder]);

        /** @var DepositReport $report */
        $report = $serializer->deserialize(
            $file,
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReport',
            'hps_csv_file'
        );

        // Archive loaded report
        if ($makeArchive) {
            $this->archiveReportFile($file, self::DEPOSIT_REPORT_FILENAME_SUFFIX);
        }

        return $report;
    }

    /**
     * @param bool $makeArchive
     * @return ReversalReport
     */
    protected function loadReversalReport($makeArchive)
    {
        if (!$file = $this->findReportFile(self::REVERSAL_REPORT_FILENAME_SUFFIX)) {
            return null;
        }

        $csvDecoder = new CsvFileDecoder();
        $denormalizer = new HPSReversalReportDenormalizer();

        $serializer = new Serializer([$denormalizer], [$csvDecoder]);

        /** @var ReversalReport $report */
        $report = $serializer->deserialize(
            $file,
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReport',
            'hps_csv_file'
        );

        // Archive loaded report
        if ($makeArchive) {
            $this->archiveReportFile($file, self::REVERSAL_REPORT_FILENAME_SUFFIX);
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
