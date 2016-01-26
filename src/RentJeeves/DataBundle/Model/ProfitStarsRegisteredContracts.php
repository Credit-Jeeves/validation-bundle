<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 * @UniqueEntity(fields={"contract", "locationId"})
 */
class ProfitStarsRegisteredContracts
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \RentJeeves\DataBundle\Entity\Contract
     *
     * @ORM\ManyToOne(targetEntity="RentJeeves\DataBundle\Entity\Contract")
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id", nullable=false)
     */
    protected $contract;

    /**
     * @var string
     *
     * @ORM\Column(name="location_id", type="string", nullable=false)
     */
    protected $locationId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     */
    public function setContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * @return string
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param string $locationId
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
