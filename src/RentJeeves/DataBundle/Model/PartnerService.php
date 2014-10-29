<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Client;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class PartnerService
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
     * @ORM\Column(
     *     name="request_name",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $requestName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\PartnerUser",
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
     *     inversedBy="partnerService"
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
}
