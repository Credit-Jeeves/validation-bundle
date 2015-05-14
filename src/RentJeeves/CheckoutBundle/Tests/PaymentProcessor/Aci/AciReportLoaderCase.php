<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Unit\Aci;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay\Report\LockboxParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportArchiver;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\AciSftpFilesDownloader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Encoder\AciPgpDecoder;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction;
use RentJeeves\TestBundle\BaseTestCase;

class AciReportLoaderCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateAciReportLoaderObjectAndObjectInstanceofRightInterface()
    {
        $logger = $this->getLoggerMock();
        $downloader = $this->getAciSftpFilesDownloaderMock();
        $decoder = $this->getAciPgpDecoderMock();
        $archiver = $this->getAciReportArchiverMock();
        $parser = $this->getLockboxParserMock();
        $reportPath = 'testPath';

        $aciLoader = new AciReportLoader($downloader, $decoder, $archiver, $parser, $reportPath, $logger);

        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\ReportLoaderInterface',
            $aciLoader
        );
    }

    /**
     * @test
     */
    public function shouldLoadReport()
    {
        $reportPath = __DIR__ . '/../../Fixtures/Aci/AciReportLoader';
        $fileName = 'testForLoaderAndDownloader.txt';

        $downloader = $this->getAciSftpFilesDownloaderMock();
        $downloader->expects($this->once())
            ->method('download');

        $decoder = $this->getAciPgpDecoderMock();
        $decoder->expects($this->once())
            ->method('decode')
            ->with($reportPath . '/' . $fileName)
            ->will($this->returnValue($encodeData = 'TestData'));

        $transaction = new DepositReportTransaction();

        $parser = $this->getLockboxParserMock();
        $parser->expects($this->once())
            ->method('parse')
            ->with($encodeData)
            ->will($this->returnValue([$transaction]));

        $archiver = $this->getAciReportArchiverMock();
        $archiver->expects($this->once())
            ->method('archive')
            ->with($reportPath . '/' . $fileName);

        $logger = $this->getLoggerMock();

        $aciLoader = new AciReportLoader($downloader, $decoder, $archiver, $parser, $reportPath, $logger);
        $report = $aciLoader->loadReport();

        $this->assertEquals($transaction, $report->getTransactions()[0]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
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
     * @return \PHPUnit_Framework_MockObject_MockObject|LockboxParser
     */
    protected function getLockboxParserMock()
    {
        return $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay\Report\LockboxParser',
            [],
            [],
            '',
            false
        );
    }
}
