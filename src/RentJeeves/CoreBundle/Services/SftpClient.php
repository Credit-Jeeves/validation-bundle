<?php

namespace RentJeeves\CoreBundle\Services;

/**
 * This class is added to be able to write UNIT tests.
 * Because to write tests for resources is hard.
 *
 * Service`s name "sftp_client"
 */
class SftpClient
{
    /**
     * @var resource
     */
    private $ssh;

    /**
     * @var resource
     */
    private $sftp;

    /**
     * @param string $host
     * @param int $port
     */
    public function sshConnect($host, $port)
    {
        if (false === is_int($port)) {
            throw new \LogicException('Parameter "port" must be an integer');
        }
        $this->ssh = ssh2_connect($host, $port);
    }

    /**
     * @param $login
     * @param $password
     */
    public function sshAuthPassword($login, $password)
    {
        if (false === is_resource($this->ssh)) {
            throw new \LogicException('SSH connection is not established. Use "sshConnect" for create it.');
        }
        ssh2_auth_password($this->ssh, $login, $password);
    }

    public function sshSftp()
    {
        if (false === is_resource($this->ssh)) {
            throw new \LogicException('SSH connection is not established. Use "sshConnect" for create it.');
        }
        $this->sftp = ssh2_sftp($this->ssh);
    }

    /**
     * @return resource
     */
    public function getSsh()
    {
        return $this->ssh;
    }

    /**
     * @return resource
     */
    public function getSftp()
    {
        return $this->sftp;
    }

    /**
     * @return string
     */
    public function getSftpPath()
    {
        if (false === is_resource($this->ssh)) {
            throw new \LogicException('SFTP subsystem is not initialized. Use "sshSftp" for initialize it.');
        }

        return 'ssh2.sftp://' . $this->getSftp();
    }
}
