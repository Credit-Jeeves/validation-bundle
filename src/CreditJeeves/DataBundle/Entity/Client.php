<?php

namespace CreditJeeves\DataBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="client")
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     */
    protected $name;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\PartnerService",
     *     mappedBy="client",
     *     cascade={"persist", "remove", "merge"}
     * )
     */
    protected $partnerService;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return PartnerService
     */
    public function getPartnerService()
    {
        return $this->partnerService;
    }

    /**
     * @param PartnerService $partnerService
     */
    public function setPartnerService($partnerService)
    {
        $this->partnerService = $partnerService;
    }

    public function __toString()
    {
        return $this->getName() ?: '';
    }
}
