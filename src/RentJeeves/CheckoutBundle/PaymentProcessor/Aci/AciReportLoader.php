<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci;

use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay\Report\AciParserInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay\Report\LockboxParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\SftpFilesDownloaderInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Encoder\FileDecoderInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciDecoderException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciReportException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;

/**
 * @DI\Service("payment_processor.aci.report_loader")
 */
class AciReportLoader implements ReportLoaderInterface
{
    /**
     * @var SftpFilesDownloaderInterface
     */
    protected $downloader;

    /**
     * @var FileDecoderInterface
     */
    protected $decoder;

    /**
     * @var LockboxParser
     */
    protected $parser;

    /**
     * @var AciReportArchiver
     */
    protected $archiver;

    /**
     * @var string
     */
    protected $reportPath;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param SftpFilesDownloaderInterface $filesDownloader
     * @param FileDecoderInterface $fileDecoder
     * @param AciReportArchiver $fileArchiver
     * @param AciParserInterface $lockboxParser
     * @param string $reportPath
     * @param LoggerInterface $logger
     *
     * @DI\InjectParams({
     *     "filesDownloader" = @DI\Inject("payment_processor.aci.files_downloader"),
     *     "fileDecoder" = @DI\Inject("payment_processor.aci.pgp_decoder"),
     *     "fileArchiver" = @DI\Inject("payment_processor.aci.report_archiver"),
     *     "lockboxParser" = @DI\Inject("payment_processor.aci.lockbox_parser"),
     *     "reportPath" = @DI\Inject("%aci.sftp.report_path%"),
     *     "logger" = @DI\Inject("logger")
     *  })
     */
    public function __construct(
        SftpFilesDownloaderInterface $filesDownloader,
        FileDecoderInterface $fileDecoder,
        AciReportArchiver $fileArchiver,
        AciParserInterface $lockboxParser,
        $reportPath,
        LoggerInterface $logger
    ) {
        $this->downloader = $filesDownloader;
        $this->reportPath = $reportPath;
        $this->archiver = $fileArchiver;
        $this->decoder = $fileDecoder;
        $this->parser = $lockboxParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        $this->downloader->download();

        $report = new PaymentProcessorReport();
        $transactions = [];

        foreach (scandir($this->reportPath) as $file) {
            $filePath = sprintf('%s/%s', $this->reportPath, $file);
            if (false === is_file($filePath)) {
                continue;
            }

            $fileTransactions = $this->getTransactionsFromFile($filePath);
            $this->archiver->archive($filePath);

            $transactions = array_merge($transactions, $fileTransactions);
        }

        $report->setTransactions($transactions);

        return $report;
    }

    /**
     * @param string $filePath
     *
     * @return PaymentProcessorReportTransaction[]
     */
    protected function getTransactionsFromFile($filePath)
    {
        try {
            $encodedData = $this->decoder->decode($filePath);
            $fileTransactions = $this->parser->parse($encodedData);
        } catch (AciDecoderException $e) {
            return [];
        } catch (AciReportException $e) {
            return [];
        }

        return $fileTransactions;
    }
}
