<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class Settings
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="precise_id_user_pwd", type="encrypt")
     */
    protected $preciseIDUserPwd;

    /**
     * @ORM\Column(name="precise_id_eai", type="encrypt")
     */
    protected $preciseIDEai;

    /**
     *
     * @ORM\Column(name="credit_profile_user_pwd", type="encrypt")
     */
    protected $creditProfileUserPwd;

    /**
     *
     * @ORM\Column(name="credit_profile_eai", type="encrypt")
     */
    protected $creditProfileEai;

    /**
     *
     * @ORM\Column(name="contract", type="text")
     */
    protected $contract;

    /**
     *
     * @ORM\Column(name="rights", type="text")
     */
    protected $rights;

    /**
     * @var string
     *
     * @ORM\Column(name="login_message", type="text", nullable=true)
     */
    protected $loginMessage;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPreciseIDUserPwd($password)
    {
        $this->preciseIDUserPwd = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreciseIDUserPwd()
    {
        return $this->preciseIDUserPwd;
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
     * @return string
     */
    public function getPreciseIDEai()
    {
        return $this->preciseIDEai;
    }

    /**
     * @param mixed $preciseIDEai
     *
     * @return $this
     */
    public function setPreciseIDEai($preciseIDEai)
    {
        $this->preciseIDEai = $preciseIDEai;

        return $this;
    }

    /**
     * @param string $netConnectPassword
     *
     * @return $this
     */
    public function setCreditProfileUserPwd($netConnectPassword)
    {
        $this->creditProfileUserPwd = $netConnectPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreditProfileUserPwd()
    {
        return $this->creditProfileUserPwd;
    }

    /**
     * @param string $netConnectEai
     *
     * @return $this
     */
    public function setCreditProfileEai($netConnectEai)
    {
        $this->creditProfileEai = $netConnectEai;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreditProfileEai()
    {
        return $this->creditProfileEai;
    }

    /**
     * @param string $contract
     *
     * @return $this
     */
    public function setContract($contract)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * @return string
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return Settings
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getLoginMessage()
    {
        return $this->loginMessage;
    }

    /**
     * @param string $loginMessage
     */
    public function setLoginMessage($loginMessage)
    {
        $this->loginMessage = $loginMessage;
    }
}
