<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Utility\Encryption;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_settings")
 * @ORM\HasLifecycleCallbacks()
 */
class Settings
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $pidkiq_password;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $pidkiq_eai;

    /**
     *
     * @ORM\Column(type="encrypt")
     */
    protected $net_connect_password;

    /**
     *
     * @ORM\Column(type="encrypt")
     */
    protected $net_connect_eai;

    /**
     *
     * @ORM\Column(type="text")
     */
    protected $contract;

    /**
     *
     * @ORM\Column(type="text")
     */
    protected $rights;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set score
     *
     * @param string $score
     * @return Score
     */
    public function setPidkiqPassword($password)
    {
        $this->pidkiq_password = $password;

        return $this;
    }

    /**
     * Get score
     *
     * @return string
     */
    public function getPidkiqPassword()
    {
        return $this->pidkiq_password;
    }

    public function setRights($rights)
    {
        $this->rights = $rights;

        return $this;
    }

    public function getRights()
    {
        return $this->rights;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * Set pidkiq_eai
     *
     * @param encrypt $pidkiqEai
     * @return Settings
     */
    public function setPidkiqEai($pidkiqEai)
    {
        $this->pidkiq_eai = $pidkiqEai;

        return $this;
    }

    /**
     * Get pidkiq_eai
     *
     * @return encrypt
     */
    public function getPidkiqEai()
    {
        return $this->pidkiq_eai;
    }

    /**
     * Set net_connect_password
     *
     * @param encrypt $netConnectPassword
     * @return Settings
     */
    public function setNetConnectPassword($netConnectPassword)
    {
        $this->net_connect_password = $netConnectPassword;

        return $this;
    }

    /**
     * Get net_connect_password
     *
     * @return encrypt
     */
    public function getNetConnectPassword()
    {
        return $this->net_connect_password;
    }

    /**
     * Set net_connect_eai
     *
     * @param encrypt $netConnectEai
     * @return Settings
     */
    public function setNetConnectEai($netConnectEai)
    {
        $this->net_connect_eai = $netConnectEai;

        return $this;
    }

    /**
     * Get net_connect_eai
     *
     * @return encrypt
     */
    public function getNetConnectEai()
    {
        return $this->net_connect_eai;
    }

    /**
     * Set contract
     *
     * @param string $contract
     * @return Settings
     */
    public function setContract($contract)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return string
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Settings
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}
