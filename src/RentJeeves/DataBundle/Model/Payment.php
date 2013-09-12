<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\PaymentType;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use \DateTime;

/**
 * @ORM\MappedSuperclass
 */
class Payment
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     inversedBy="payments"
     * )
     * @ORM\JoinColumn(
     *     name="contract_id",
     *     referencedColumnName="id"
     * )
     * @var Contract
     */
    protected $contract;

    /**
     * @ORM\Column(type="PaymentType")
     * @var PaymentType
     */
    protected $type;

    /**
     * @ORM\Column(type="PaymentStatus")
     * @var PaymentStatus
     */
    protected $status;

    /**
     * @ORM\Column(type="decimal")
     * @var double
     */
    protected $amount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $dueDate = null;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $startMonth;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $startYear;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $endMonth = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $endYear = null;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     */
    protected $updatedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param PaymentType $type
     * @return Payment
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return PaymentType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status
     *
     * @param PaymentStatus $status
     * @return Payment
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return PaymentStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set dueDate
     *
     * @param integer $dueDate
     * @return Payment
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return integer
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set startMonth
     *
     * @param integer $startMonth
     * @return Payment
     */
    public function setStartMonth($startMonth)
    {
        $this->startMonth = $startMonth;

        return $this;
    }

    /**
     * Get startMonth
     *
     * @return integer
     */
    public function getStartMonth()
    {
        return $this->startMonth;
    }

    /**
     * Set startYear
     *
     * @param integer $startYear
     * @return Payment
     */
    public function setStartYear($startYear)
    {
        $this->startYear = $startYear;

        return $this;
    }

    /**
     * Get startYear
     *
     * @return integer
     */
    public function getStartYear()
    {
        return $this->startYear;
    }

    /**
     * Set endMonth
     *
     * @param integer $endMonth
     * @return Payment
     */
    public function setEndMonth($endMonth)
    {
        $this->endMonth = $endMonth;

        return $this;
    }

    /**
     * Get endMonth
     *
     * @return integer
     */
    public function getEndMonth()
    {
        return $this->endMonth;
    }

    /**
     * Set endYear
     *
     * @param integer $endYear
     * @return Payment
     */
    public function setEndYear($endYear)
    {
        $this->endYear = $endYear;

        return $this;
    }

    /**
     * Get endYear
     *
     * @return integer
     */
    public function getEndYear()
    {
        return $this->endYear;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Payment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Payment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set contract
     *
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     * @return Payment
     */
    public function setContract(\RentJeeves\DataBundle\Entity\Contract $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \RentJeeves\DataBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }
}
