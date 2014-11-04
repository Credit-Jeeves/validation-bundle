<?php

namespace CreditJeeves\DataBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\Partner;

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
     *     targetEntity="RentJeeves\DataBundle\Entity\Partner",
     *     mappedBy="client",
     *     cascade={"persist", "remove", "merge"}
     * )
     */
    protected $partner;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param Partner $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
    }

    public function __toString()
    {
        return $this->getName() ?: '';
    }
}
