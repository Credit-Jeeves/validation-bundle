<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class AciCollectPayContractBilling
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \RentJeeves\DataBundle\Entity\Contract
     *
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     inversedBy="aciCollectPayContractBilling"
     * )
     * @ORM\JoinColumn(
     *     name="contract_id",
     *     referencedColumnName="id"
     * )
     */
    protected $contract;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
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
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     */
    public function setContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @param DateTime $createdAt
     * @return static
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
