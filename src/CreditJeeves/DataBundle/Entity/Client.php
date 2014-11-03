<?php

namespace CreditJeeves\DataBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\PartnerApplication;

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
     *     targetEntity="RentJeeves\DataBundle\Entity\PartnerApplication",
     *     mappedBy="client",
     *     cascade={"persist", "remove", "merge"}
     * )
     */
    protected $partnerApplication;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return PartnerApplication
     */
    public function getPartnerApplication()
    {
        return $this->partnerApplication;
    }

    /**
     * @param PartnerApplication $partnerService
     */
    public function setPartnerApplication($partnerService)
    {
        $this->partnerApplication = $partnerService;
    }

    public function __toString()
    {
        return $this->getName() ?: '';
    }
}
