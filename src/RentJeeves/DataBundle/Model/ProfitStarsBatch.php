<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CreditJeeves\DataBundle\Entity\Holding as HoldingEntity;
use RentJeeves\DataBundle\Enum\ProfitStarsBatchStatus;

/**
 * @ORM\MappedSuperclass
 */
abstract class ProfitStarsBatch
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
     * @var HoldingEntity
     *
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Holding")
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=false)
     */
    protected $holding;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="batch_number", length=255, unique=true)
     */
    protected $batchNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="ProfitStarsBatchStatus")
     */
    protected $status = ProfitStarsBatchStatus::OPEN;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBatchNumber()
    {
        return $this->batchNumber;
    }

    /**
     * @param string $batchNumber
     */
    public function setBatchNumber($batchNumber)
    {
        $this->batchNumber = $batchNumber;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
