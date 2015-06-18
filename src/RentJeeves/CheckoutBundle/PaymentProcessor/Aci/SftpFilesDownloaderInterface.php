<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci;

interface SftpFilesDownloaderInterface
{
    /**
     * Download files from remote SFTP server
     */
    public function download();

    /**
     * Return path to downloaded files
     *
     * @return string
     */
    public function getDownloadDirPath();

    /**
     * Connect to an SSH server
     */
    public function initializeSshConnect();

    /**
     * Initialize SFTP subsystems
     */
    public function initializeSftpSubsystem();
}
