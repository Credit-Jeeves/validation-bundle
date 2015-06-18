<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportArchiver;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\AciSftpFilesDownloader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Encoder\AciPgpDecoder;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\AdjustmentParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\ResponseParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\ReportLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectReversalReportTransaction;
use RentJeeves\TestBundle\BaseTestCase;

class ReportLoaderCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnReportWithAllTransactionsFromAllCorrectFiles()
    {
        $pathToFile = $this->getFileLocator()->locate(
            '@RjCheckoutBundle/Tests/Fixtures/Aci/PayAnyoneReportLoader/adjustFile.xml'
        );
        $pathToFile2 = $this->getFileLocator()->locate(
            '@RjCheckoutBundle/Tests/Fixtures/Aci/PayAnyoneReportLoader/responseEmptyFile.xml'
        );
        $pathToFile3 = $this->getFileLocator()->locate(
            '@RjCheckoutBundle/Tests/Fixtures/Aci/PayAnyoneReportLoader/responseFile.xml'
        );
        $reportPath = substr($pathToFile, 0, strripos($pathToFile, '/'));

        $downloader = $this->getAciSftpFilesDownloaderMock();
        $downloader->expects($this->once())
            ->method('getDownloadDirPath')
            ->will($this->returnValue($reportPath));

        $decoder = $this->getAciPgpDecoderMock();

        $decoder->expects($this->at(0))
            ->method('decode')
            ->with($this->equalTo($pathToFile))
            ->will($this->returnValue($decodedAdjustFile = 'decodedAdjustFile'));
        $decoder->expects($this->at(1))
            ->method('decode')
            ->with($this->equalTo($pathToFile2))
            ->will($this->returnValue($decodedResponseFile1 = 'decodedResponseFile'));
        $decoder->expects($this->at(2))
            ->method('decode')
            ->with($this->equalTo($pathToFile3))
            ->will($this->returnValue($decodedResponseFile2 = 'decodedResponseFile2'));
        $decoder->expects($this->exactly(3))
            ->method('decode');

        $archiver = $this->getAciReportArchiverMock();
        $archiver->expects($this->exactly(3))
            ->method('archive')
            ->with(
                $this->logicalOr(
                    $this->equalTo($pathToFile),
                    $this->equalTo($pathToFile2),
                    $this->equalTo($pathToFile3)
                )
            );

        $responseParserAnswer = [new PayDirectResponseReportTransaction()];
        $responseParser = $this->getResponseParserMock();
        $responseParser->expects($this->exactly(2))
            ->method('parse')
            ->with(
                $this->logicalOr(
                    $this->equalTo($decodedResponseFile1),
                    $this->equalTo($decodedResponseFile2)
                )
            )
            ->will($this->returnValue($responseParserAnswer));

        $adjustmentParserAnswer = [new PayDirectReversalReportTransaction()];
        $adjustmentParser = $this->getAdjustmentParserMock();
        $adjustmentParser->expects($this->once())
            ->method('parse')
            ->with($this->equalTo($decodedAdjustFile))
            ->will($this->returnValue($adjustmentParserAnswer));

        $reportLoader = new ReportLoader(
            $downloader,
            $decoder,
            $archiver,
            $responseParser,
            $adjustmentParser,
            $this->getLoggerMock()
        );

        $report = $reportLoader->loadReport();

        $this->assertEquals(3, count($report->getTransactions())); // 2 Response + 1 adjustment
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AciPgpDecoder
     */
    protected function getAciPgpDecoderMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Encoder\AciPgpDecoder',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AciReportArchiver
     */
    protected function getAciReportArchiverMock()
    {
        return $this->getMock('\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportArchiver', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResponseParser
     */
    protected function getResponseParserMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\ResponseParser',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AdjustmentParser
     */
    protected function getAdjustmentParserMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\AdjustmentParser',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AciSftpFilesDownloader
     */
    protected function getAciSftpFilesDownloaderMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\AciSftpFilesDownloader',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }
}
