<?php

namespace RentJeeves\CoreBundle\Sftp;

use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Exception\SftpFileManagerException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Service`s name "sftp_file_manager" (abstract)
 */
class SftpFileManager
{
    const NUMBER_OF_RETRY = 5;

    /**
     * @var SftpClient
     */
    protected $sftpClient;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @param string          $host
     * @param string          $port
     * @param LoggerInterface $logger
     * @param SftpClient      $sftpClient
     */
    public function __construct(
        $host,
        $port,
        LoggerInterface $logger,
        SftpClient $sftpClient
    ) {
        $this->host = $host;
        $this->port = (int) $port;
        $this->logger = $logger;
        $this->sftpClient = $sftpClient;
    }

    /**
     * @param string $login
     * @param string $publicKey
     * @param string $privateKey
     */
    public function setKeysCredentials($login, $publicKey, $privateKey)
    {
        $this->login = $login;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * @param string $login
     * @param string $password
     */
    public function setPasswordCredentials($login,  $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @param string $inputPathToFile  path to file on remote server
     * @param string $outputPathToFile path to file on local server
     *
     * @throws SftpFileManagerException
     */
    public function download($inputPathToFile, $outputPathToFile)
    {
        $this->configureRemoteConnection();
        $this->logger->debug(sprintf('Trying to download file "%s".', $inputPathToFile));
        $data = $this->downloadData($inputPathToFile);
        try {
            $result = file_put_contents($outputPathToFile, $data);
        } catch (\Exception $e) {
            throw new SftpFileManagerException($e->getMessage());
        }

        if (false === $result) {
            $this->logger->debug(
                $message = sprintf(
                    'file_put_contents(%s) returned FALSE. Pls check parameters.',
                    $outputPathToFile
                )
            );
            throw new SftpFileManagerException($message);
        }
        $this->logger->debug('File was downloaded successfully.');
    }

    /**
     * @param string $inputFilePath path to file on remote server
     *
     * @throws SftpFileManagerException
     *
     * @return mixed
     */
    public function downloadData($inputFilePath)
    {
        $this->configureRemoteConnection();
        $this->logger->debug(sprintf('Trying to download data "%s".', $inputFilePath));
        $remotePathToFile = $this->getSftpPath() . $inputFilePath;
        if (false === file_exists($remotePathToFile) || false === is_file($remotePathToFile)) {
            $this->logger->debug($message = sprintf('File %s not found.', $remotePathToFile));
            throw new SftpFileManagerException($message);
        }
        try {
            $data = file_get_contents($remotePathToFile);
        } catch (\Exception $e) {
            throw new SftpFileManagerException($e->getMessage());
        }

        if (false === $data) {
            $this->logger->debug(
                $message = sprintf(
                    'file_get_contents(%s) returned FALSE. Pls check parameters.',
                    $this->getSftpPath() . $inputFilePath
                )
            );
            throw new SftpFileManagerException($message);
        }

        $this->logger->debug('Data was downloaded successfully.');

        return $data;
    }

    /**
     * @param mixed  $data
     * @param string $pathToFile
     *
     * @throws SftpFileManagerException
     */
    public function upload($data, $pathToFile)
    {
        $this->configureRemoteConnection();
        $this->logger->debug(sprintf('Trying to upload data to file "%s".', $pathToFile));
        $remotePathToFile = $this->getSftpPath() . $pathToFile;
        $dir = dirname($remotePathToFile);
        if (false === file_exists($dir)) {
            $this->logger->debug($message = sprintf('Dir %s doesn`t not exists.', $dir));
            throw new SftpFileManagerException($message);
        }

        try {
            $result = file_put_contents($remotePathToFile, $data);
        } catch (\Exception $e) {
            throw new SftpFileManagerException($e->getMessage());
        }

        if (false === $result) {
            $this->logger->debug(
                $message = sprintf(
                    'file_put_contents(%s) returned FALSE. Pls check parameters.',
                    $this->getSftpPath() . $remotePathToFile
                )
            );
            throw new SftpFileManagerException($message);
        }

        $this->logger->debug('Data was uploaded successfully.');
    }

    /**
     * @param string $inputPathToFile  path to file on remote server
     * @param string $outputPathToFile path to dir on remote server, which moving file
     *
     * @throws SftpFileManagerException
     */
    public function move($inputPathToFile, $outputPathToFile)
    {
        $this->configureRemoteConnection();

        $inputPathToFile = $this->getSftpPath() . $inputPathToFile;
        $outputPathToFile = $this->getSftpPath() . $outputPathToFile;

        if (false === file_exists($inputPathToFile) || false === is_file($inputPathToFile)) {
            $this->logger->debug($message = sprintf('File %s not found.', $inputPathToFile));
            throw new SftpFileManagerException($message);
        }

        $dir = dirname($outputPathToFile);
        if (false === file_exists($dir) || false === is_dir($dir)) {
            $this->logger->debug($message = sprintf('Dir %s doesn`t not exists.', $outputPathToFile));
            throw new SftpFileManagerException($message);
        }

        $filesystem = new Filesystem();
        try {
            $filesystem->rename($inputPathToFile, $outputPathToFile);
        } catch (\Exception $e) {
            $this->logger->debug(
                $message = sprintf(
                    'Move remote file - FAILED: %s',
                    $e->getMessage()
                )
            );
            throw new SftpFileManagerException($message);
        }
        $this->logger->debug('File was moved successfully.');
    }

    protected function configureRemoteConnection()
    {
        if (false === is_resource($this->getSsh())) {
            $this->logger->debug('The attempt to initialize SSH connection.');
            $this->initializeSshConnect();
        }
        if (false === is_resource($this->getSftp())) {
            $this->logger->debug('Initialize SFTP subsystem.');
            $this->initializeSftpSubsystem();
        }
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
     * @throws SftpFileManagerException if cant initialize connection after NUMBER_OF_RETRY attempts
     */
    protected function initializeSshConnect()
    {
        $i = 1;
        while ($i <= self::NUMBER_OF_RETRY) {
            try {
                $this->logger->debug(sprintf('The %s attempt to initialize SSH connection.', $i));
                $this->sftpClient->sshConnect($this->host, $this->port);
                $this->authenticateSftpClient();
                break;
            } catch (\Exception $e) {
                $this->logger->debug(
                    sprintf(
                        'The %s attempt to initialize SSH connection - FAILED: %s',
                        $i,
                        $e->getMessage()
                    )
                );
                if ($i === self::NUMBER_OF_RETRY) {
                    throw new SftpFileManagerException($e->getMessage());
                }
                $this->logger->debug('Waiting before attempting to initialize the SSH connection.');
                sleep((int) exp($i));
                $i++;
            }
        }
    }

    /**
     * @throws SftpFileManagerException if cant initialize sftp system after NUMBER_OF_RETRY attempts
     */
    protected function initializeSftpSubsystem()
    {
        try {
            $this->logger->debug('Initialize SFTP subsystem.');
            $this->sftpClient->sshSftp();
        } catch (\Exception $e) {
            $this->logger->debug(
                sprintf(
                    'Initialize SFTP subsystem - FAILED : %s',
                    $e->getMessage()
                )
            );
            throw new SftpFileManagerException($e->getMessage());
        }
    }

    /**
     * @throws SftpFileManagerException
     *
     * @return string
     */
    protected function getSftpPath()
    {
        if (false === is_resource($this->getSftp())) {
            throw new SftpFileManagerException('SFTP subsystem is not initialized. Use "sshSftp" for initialize it.');
        }

        return 'ssh2.sftp://' . $this->getSftp();
    }

    /**
     * @throws SftpFileManagerException
     */
    protected function authenticateSftpClient()
    {
        if (null !== $this->publicKey && null !== $this->privateKey && null !== $this->login) {
            $this->sftpClient->sshAuthKeyFiles($this->login, $this->publicKey, $this->privateKey);
        } elseif (null !== $this->password && null !== $this->login) {
            $this->sftpClient->sshAuthPassword($this->login, $this->password);
        } else {
            throw new SftpFileManagerException(
                'Cant do sftp auth without any auth parameters.' .
                'Pls use "setKeysCredentials" or "setPasswordCredentials" for set auth parameters.'
            );
        }
    }
}
