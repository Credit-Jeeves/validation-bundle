<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Downloader;

use Monolog\Logger;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\SftpFilesDownloaderInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciDownloaderException;
use RentJeeves\CoreBundle\Services\SftpClient;
use Symfony\Component\Filesystem\Filesystem;

class AciSftpFilesDownloader implements SftpFilesDownloaderInterface
{
    const NUMBER_OF_RETRY = 5;

    /**
     * @var SftpClient
     */
    private $sftpClient;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $reportPath;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param string $host
     * @param string $port
     * @param string $login
     * @param string $password
     * @param string $reportPath
     * @param Logger $logger
     * @param SftpClient $sftpClient
     */
    public function __construct($host, $port, $login, $password, $reportPath, Logger $logger, SftpClient $sftpClient)
    {
        $this->host = $host;
        $this->port = (int) $port;
        $this->login = $login;
        $this->password = $password;
        $this->reportPath = $reportPath;
        $this->logger = $logger;
        $this->sftpClient = $sftpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeSshConnect()
    {
        $i = 1;
        while ($i <= self::NUMBER_OF_RETRY) {
            try {
                $this->logger->debug(sprintf('ACI: The %s attempt to initialize SSH connection.', $i));
                $this->sftpClient->sshConnect($this->host, $this->port);
                $this->sftpClient->sshAuthPassword($this->login, $this->password);
                break;
            } catch (\Exception $e) {
                $this->logger->debug(
                    sprintf(
                        'ACI: The %s attempt to initialize SSH connection - FAILED: %s',
                        $i,
                        $e->getMessage()
                    )
                );
                if ($i === self::NUMBER_OF_RETRY) {
                    throw new AciDownloaderException($e->getMessage());
                }
                $this->logger->debug('ACI: Waiting before attempting to initialize the SSH connection.');
                sleep((int) exp($i));
                $i++;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initializeSftpSubsystem()
    {
        try {
            $this->logger->debug('ACI: Initialize SFTP subsystem.');
            $this->sftpClient->sshSftp();
        } catch (\Exception $e) {
            $this->logger->debug(
                sprintf(
                    'ACI: Initialize SFTP subsystem - FAILED : %s',
                    $e->getMessage()
                )
            );
            throw new AciDownloaderException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function download()
    {
        if (false === is_resource($this->getSsh())) {
            $this->initializeSshConnect();
        }
        if (false === is_resource($this->getSftp())) {
            $this->initializeSftpSubsystem();
        }

        foreach ($this->getAllFilesFromSftpRootDirectory() as $remoteFile) {
            $remoteFileName = basename($remoteFile);
            $outputPath = sprintf('%s/%s', $this->reportPath, $remoteFileName);
            $this->downloadRemoteFile($remoteFile, $outputPath);
            $this->archiveRemoteFile($remoteFile);
        }
    }

    /**
     * @param string $pathToDir
     *
     * @return array with paths for files on SFTP server
     *
     * @throws AciDownloaderException
     */
    protected function getAllFilesFromSftpByPath($pathToDir)
    {
        $path = $this->getSftpPathToRemoteDir($pathToDir);

        if (false === is_dir($path)) {
            throw new AciDownloaderException('Invalid path to the directory');
        }

        $remoteFiles = [];

        foreach (scandir($path) as $item) {
            $fullItemPath = $path . '/' . $item;
            if (true === is_file($fullItemPath) && true === is_readable($fullItemPath)) {
                $remoteFiles[] = $fullItemPath;
            }
        }

        return $remoteFiles;
    }

    /**
     * @return  array
     *
     * @throws AciDownloaderException
     */
    protected function getAllFilesFromSftpRootDirectory()
    {
        return $this->getAllFilesFromSftpByPath('/.');
    }

    /**
     * @param string $inputFilePath
     * @param string $outFilePath
     *
     * @throws AciDownloaderException
     */
    protected function downloadRemoteFile($inputFilePath, $outFilePath)
    {
        try {
            $this->logger->debug(sprintf('ACI: Trying to download report "%s".', $inputFilePath));
            $fileData = file_get_contents($inputFilePath);
            file_put_contents($outFilePath, $fileData);
        } catch (\Exception $e) {
            $this->logger->debug(
                sprintf(
                    'ACI: Download report - FAILED: %s',
                    $e->getMessage()
                )
            );
            throw new AciDownloaderException($e->getMessage());
        }
    }

    /**
     * @param string $remoteFile
     */
    protected function archiveRemoteFile($remoteFile)
    {
        $remoteFileName = basename($remoteFile);
        $pathToArchiveDir = $this->getSftpPathToRemoteDir('/archive');
        $archiveFilename = sprintf('%s/%s', $pathToArchiveDir, $remoteFileName);

        $this->logger->debug(sprintf('ACI: Trying to archive remote file "%s".', $archiveFilename));
        $filesystem = new Filesystem();
        try {
            if (false === is_dir($pathToArchiveDir)) {
                $filesystem->mkdir($pathToArchiveDir);
            }
            $filesystem->rename($remoteFile, $archiveFilename);
        } catch (\Exception $e) {
            $this->logger->debug(
                sprintf(
                    'ACI: Archive remote file - FAILED: %s',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param string $pathToDir
     *
     * @return string
     */
    protected function getSftpPathToRemoteDir($pathToDir)
    {
        return $this->sftpClient->getSftpPath() . $pathToDir;
    }

    /**
     * @return resource
     */
    protected function getSsh()
    {
        return $this->sftpClient->getSsh();
    }

    /**
     * @return resource
     */
    protected function getSftp()
    {
        return $this->sftpClient->getSftp();
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadDirPath()
    {
        return $this->reportPath;
    }
}
