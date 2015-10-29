<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportArchiver;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\FileDecoderInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\AdjustmentParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\ResponseParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\SftpFilesDownloaderInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ReportLoader
{
    const REGEX_RESPONSE_FILE = 'response*';
    const REGEX_ADJUSTMENT_FILE = 'adjust*';

    /**
     * @var SftpFilesDownloaderInterface
     */
    protected $downloader;

    /**
     * @var FileDecoderInterface
     */
    protected $decoder;

    /**
     * @var ResponseParser
     */
    protected $responseParser;

    /**
     * @var AdjustmentParser
     */
    protected $adjustmentParser;

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
     * @param ResponseParser $responseParser
     * @param AdjustmentParser $adjustmentParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        SftpFilesDownloaderInterface $filesDownloader,
        FileDecoderInterface $fileDecoder,
        AciReportArchiver $fileArchiver,
        ResponseParser $responseParser,
        AdjustmentParser $adjustmentParser,
        LoggerInterface $logger
    ) {
        $this->downloader = $filesDownloader;
        $this->reportPath = $filesDownloader->getDownloadDirPath();
        $this->archiver = $fileArchiver;
        $this->decoder = $fileDecoder;
        $this->responseParser = $responseParser;
        $this->adjustmentParser = $adjustmentParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        $this->downloader->download();

        $allTransactions = array_merge(
            $this->getTransactionsFromAdjustmentFiles(),
            $this->getTransactionsFromResponseFiles()
        );

        $report = new PaymentProcessorReport();
        $report->setTransactions($allTransactions);

        return $report;
    }

    /**
     * Return all transactions from all response files
     *
     * @return PayDirectResponseReportTransaction[]
     */
    protected function getTransactionsFromResponseFiles()
    {
        $responseTransactions = [];

        $finder = new Finder();
        $finder
            ->files()
            ->name(self::REGEX_RESPONSE_FILE)
            ->in($this->reportPath)
            ->depth(0);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $fileTransactions = $this->getTransactionsFromResponseFile($file->getRealPath());
            $responseTransactions = array_merge($responseTransactions, $fileTransactions);
        }

        return $responseTransactions;
    }

    /**
     * Return all transactions from 1 response file
     *
     * @param string $filePath
     *
     * @return PayDirectResponseReportTransaction[]
     */
    protected function getTransactionsFromResponseFile($filePath)
    {
        $decodedData = $this->decoder->decode($filePath);
        $transactions = $this->responseParser->parse($decodedData);
        $this->archiver->archive($filePath);

        return $transactions;
    }

    /**
     * Return all transactions from all adjustment files
     *
     * @return PaymentProcessorReportTransaction[]
     */
    protected function getTransactionsFromAdjustmentFiles()
    {
        $responseTransactions = [];

        $finder = new Finder();
        $finder
            ->files()
            ->name(self::REGEX_ADJUSTMENT_FILE)
            ->in($this->reportPath)
            ->depth(0);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $fileTransactions = $this->getTransactionsFromAdjustmentFile($file->getRealPath());
            $responseTransactions = array_merge($responseTransactions, $fileTransactions);
        }

        return $responseTransactions;
    }

    /**
     * Return all transactions from 1 adjustment file
     *
     * @param string $filePath
     *
     * @return PaymentProcessorReportTransaction[]
     */
    protected function getTransactionsFromAdjustmentFile($filePath)
    {
        $decodedData = $this->decoder->decode($filePath);
        $transactions = $this->adjustmentParser->parse($decodedData);
        $this->archiver->archive($filePath);

        return $transactions;
    }
}
