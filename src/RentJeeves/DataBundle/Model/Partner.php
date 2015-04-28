<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Client;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Partner
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(
     *      type="string",
     *      unique=true
     * )
     */
    protected $name;

    /**
     * @ORM\Column(name="logo_name", type="string", nullable=true)
     */
    protected $logoName;

    /**
     * @ORM\Column(name="login_url", type="string", nullable=true)
     */
    protected $loginUrl;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="is_powered_by", type="boolean")
     */
    protected $isPoweredBy;

    /**
     * @ORM\Column(
     *     name="request_name",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $requestName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\PartnerUserMapping",
     *     mappedBy="partner",
     *     cascade={"all"}
     * )
     *
     * @var ArrayCollection
     */
    protected $partnerUsers;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Client",
     *     inversedBy="partner"
     * )
     * @ORM\JoinColumn(
     *     name="client_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    protected $client;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $requestName
     */
    public function setRequestName($requestName)
    {
        $this->requestName = $requestName;
    }

    /**
     * @return string
     */
    public function getRequestName()
    {
        return $this->requestName;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return ArrayCollection
     */
    public function getPartnerUsers()
    {
        return $this->partnerUsers;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    public function __toString()
    {
        return $this->getName() ?: '';
    }

    /**
     * @return string
     */
    public function getLogoName()
    {
        return $this->logoName;
    }

    /**
     * @param string $logoName
     */
    public function setLogoName($logoName)
    {
        $this->logoName = $logoName;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * @param string $loginUrl
     */
    public function setLoginUrl($loginUrl)
    {
        $this->loginUrl = $loginUrl;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return boolean
     */
    public function isPoweredBy()
    {
        return $this->isPoweredBy;
    }

    /**
     * @param boolean $isPoweredBy
     */
    public function setIsPoweredBy($isPoweredBy)
    {
        $this->isPoweredBy = $isPoweredBy;
    }
}
