<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\Downloader;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\AciSftpFilesDownloader;
use RentJeeves\CoreBundle\Services\SftpClient;
use RentJeeves\TestBundle\BaseTestCase;

class AciSftpFilesLoaderCase extends BaseTestCase
{
    protected $host = 'www.test.com';
    protected $port = 20;
    protected $login = 'test';
    protected $password = 'test';
    protected $reportPath = '/tmp/';

    /**
     * @test
     */
    public function shouldInstanceofRightInterface()
    {
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader\SftpFilesDownloaderInterface',
            $this->getAciSftpFilesDownloaderMock()
        );
    }

    /**
     * @test
     */
    public function shouldCallSshConnectAndSshAuthPasswordForInitializeSshConnect()
    {
        $logger = $this->getContainer()->get('logger');
        $sftpClient = $this->getSftpClientMock();

        $sftpClient
            ->expects($this->once())
            ->method('sshConnect')
            ->with($this->host, $this->port);
        $sftpClient
            ->expects($this->once())
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
    public function shouldCallSshSftpForInitializeSftpSubsystem()
    {
        $logger = $this->getContainer()->get('logger');
        $sftpClient = $this->getSftpClientMock();
        $sftpClient
            ->expects($this->once())
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
     * @return SftpClient
     */
    protected function getSftpClientMock()
    {
        return $this->getMock('\RentJeeves\CoreBundle\Services\SftpClient', [], [], '', false);
    }

    /**
     * @return AciSftpFilesDownloader
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
}
