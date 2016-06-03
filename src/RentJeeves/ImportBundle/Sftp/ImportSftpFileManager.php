<?php

namespace RentJeeves\ImportBundle\Sftp;

use RentJeeves\CoreBundle\Sftp\SftpFileManager;

/**
 * Service`s name "import.property.sftp_file_manager"
 */
class ImportSftpFileManager extends SftpFileManager
{
    /**
     * @param string $login
     * @param string $publicKeyData
     * @param string $privateKeyData
     */
    public function setKeysCredentials($login, $publicKeyData, $privateKeyData)
    {
        $this->login = $login;

        $publicKeyFilePath = tempnam(sys_get_temp_dir(), 'public_');
        $privateKeyFilePath = tempnam(sys_get_temp_dir(), 'private_');

        file_put_contents($publicKeyFilePath, $publicKeyData);
        file_put_contents($privateKeyFilePath, $privateKeyData);

        $this->publicKey = $publicKeyFilePath;
        $this->privateKey = $privateKeyFilePath;
    }

    public function disconnect()
    {
        unlink($this->publicKey);
        unlink($this->privateKey);
    }
}
