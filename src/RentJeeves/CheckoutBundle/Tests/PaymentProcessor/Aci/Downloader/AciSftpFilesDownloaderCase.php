<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Unit\Aci\Downloader;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\AciSftpFilesDownloader;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;

class AciSftpFilesDownloaderCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected $host = 'testHost';

    /**
     * @var string
     */
    protected $port = 8080;

    /**
     * @var string
     */
    protected $login = 'testLogin';

    /**
     * @var string
     */
    protected $password = 'testPassword';

    /**
     * @var string
     */
    protected $reportPath = 'testReportPath';

    /**
     * @test
     */
    public function shouldCreateAciSftpFilesDownloaderObjectAndObjectInstanceofRightInterface()
    {
        $logger = $this->getLoggerMock();
        $sftpClient = $this->getSftpClientMock();

        $downloader = new AciSftpFilesDownloader(
            $this->host,
            $this->port,
            $this->login,
            $this->password,
            $this->reportPath,
            $logger,
            $sftpClient
        );

        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\SftpFilesDownloaderInterface',
            $downloader
        );
    }

    /**
     * @test
     */
    public function shouldInitializeSshConnectAndAddLog()
    {
        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('debug')
            ->with('ACI: The 1 attempt to initialize SSH connection.');

        $sftpClient = $this->getSftpClientMock();
        $sftpClient->expects($this->once())
            ->method('sshConnect')
            ->with($this->host, $this->port);
        $sftpClient->expects($this->once())
            ->method('sshAuthPassword')
            ->with($this->login, $this->password);

        $downloader = new AciSftpFilesDownloader(
            $this->host,
            $this->port,
            $this->login,
            $this->password,
            $this->reportPath,
            $logger,
            $sftpClient
        );

        $downloader->initializeSshConnect();
    }

    /**
     * @test
     */
    public function shouldInitializeSftpSubsystemAndAddLog()
    {
        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('debug')
            ->with('ACI: Initialize SFTP subsystem.');

        $sftpClient = $this->getSftpClientMock();
        $sftpClient->expects($this->once())
            ->method('sshSftp');

        $downloader = new AciSftpFilesDownloader(
            $this->host,
            $this->port,
            $this->login,
            $this->password,
            $this->reportPath,
            $logger,
            $sftpClient
        );

        $downloader->initializeSftpSubsystem();
    }

    /**
     * @test
     */
    public function shouldInitSftpAndDownloadReportsAndAddLog()
    {
        $reportPath = __DIR__ . '/../../../Fixtures/Aci/AciReportLoader';
        $reportArchivePath = __DIR__ . '/../../../Fixtures/Aci/AciReportLoader/archive';

        $fileName = 'testForLoaderAndDownloader.txt';

        $this->assertEquals(['.', '..', '.gitkeep'], scandir($reportArchivePath));

        $logger = $this->getLoggerMock();
        $logger->expects($this->exactly(4))
            ->method('debug');
        $sftpClient = $this->getSftpClientMock();
        $sftpClient->expects($this->once())
            ->method('sshConnect')
            ->with($this->host, $this->port);
        $sftpClient->expects($this->once())
            ->method('sshAuthPassword')
            ->with($this->login, $this->password);
        $sftpClient->expects($this->once())
            ->method('sshSftp');

        $map = [
            [$reportPath],
            [$reportArchivePath]
        ];

        $sftpClient->expects($this->exactly(2))
            ->method('getSftpPath')
            ->will($this->returnValueMap($map));

        $downloader = new AciSftpFilesDownloader(
            $this->host,
            $this->port,
            $this->login,
            $this->password,
            $reportPath,
            $logger,
            $sftpClient
        );

        $downloader->download();
        // file moved to archive (local)
        $this->assertEquals(['.', '..', '.gitkeep', $fileName], scandir($reportArchivePath));

        //return file to correct dir
        $filesystem = new Filesystem();
        $filesystem->rename($reportArchivePath . '/' . $fileName, $reportPath . '/' . $fileName);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\CoreBundle\Services\SftpClient
     */
    protected function getSftpClientMock()
    {
        return $this->getMock('\RentJeeves\CoreBundle\Services\SftpClient', [], [], '', false);
    }
}
