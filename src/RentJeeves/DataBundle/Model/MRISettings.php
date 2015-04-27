<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Holding;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class MRISettings
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="url",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\Url()
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(
     *     name="client_id",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $clientId;

    /**
     * @ORM\Column(
     *     name="user",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $user;

    /**
     * @ORM\Column(
     *     name="password",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(
     *     name="database_name",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $databaseName;

    /**
     * @ORM\Column(
     *     name="partner_key",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $partnerKey;

    /**
     * @ORM\Column(
     *     name="hash",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $hash;

    /**
     * @ORM\Column(
     *     name="site_id",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $siteId;

    /**
     * @ORM\Column(
     *     name="charge_code",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $chargeCode;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="mriSettings",
     *     cascade={"persist", "merge"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=false, unique=true)
     * @var Holding
     */
    protected $holding;

    /**
     * @return string
     */
    public function getChargeCode()
    {
        return $this->chargeCode;
    }

    /**
     * @param string $chargeCode
     */
    public function setChargeCode($chargeCode)
    {
        $this->chargeCode = $chargeCode;
    }

    /**
     * @return string
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param string $siteId
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @param Holding $holding
     */
    public function setHolding(Holding $holding)
    {
        $this->holding = $holding;
    }

    /**
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getPartnerKey()
    {
        return $this->partnerKey;
    }

    /**
     * @param string $partnerKey
     */
    public function setPartnerKey($partnerKey)
    {
        $this->partnerKey = $partnerKey;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
