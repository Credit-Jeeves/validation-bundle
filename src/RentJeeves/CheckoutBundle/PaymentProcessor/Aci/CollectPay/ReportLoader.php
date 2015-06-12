<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportArchiver;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\Report\CollectPayParserInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\Report\LockboxParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\FileDecoderInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\SftpFilesDownloaderInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciDecoderException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciReportException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ReportLoader
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
     * @param CollectPayParserInterface $lockboxParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        SftpFilesDownloaderInterface $filesDownloader,
        FileDecoderInterface $fileDecoder,
        AciReportArchiver $fileArchiver,
        CollectPayParserInterface $lockboxParser,
        LoggerInterface $logger
    ) {
        $this->downloader = $filesDownloader;
        $this->reportPath = $filesDownloader->getDownloadDirPath();
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

        $finder = new Finder();
        /** @var SplFileInfo $file */
        foreach ($finder->files()->in($this->reportPath)->depth(0) as $file) {
            $filePath = $file->getRealPath();
            $fileTransactions = $this->getTransactionsFromReportFile($filePath);
            $this->archiver->archive($filePath);

            $transactions = array_merge($transactions, $fileTransactions);
        }

        $report->setTransactions($transactions);

        return $report;
    }

    /**
     * @param string $reportFilePath
     *
     * @return PaymentProcessorReportTransaction[]
     */
    protected function getTransactionsFromReportFile($reportFilePath)
    {
        try {
            $decodedData = $this->decoder->decode($reportFilePath);
            $reportFileTransactions = $this->parser->parse($decodedData);
        } catch (AciDecoderException $e) {
            return [];
        } catch (AciReportException $e) {
            return [];
        }

        return $reportFileTransactions;
    }
}
