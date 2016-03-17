<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Entity\DepositAccount as DepositAccountEntity;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * @ORM\MappedSuperclass
 */
class PaymentHistory extends AbstractLogEntry
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @var string $objectId
     *
     * @ORM\Column(name="object_id", type="bigint", nullable=true)
     */
    protected $objectId;

    /**
     * @ORM\ManyToOne(targetEntity="RentJeeves\DataBundle\Entity\Payment", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", nullable=true)
     */
    protected $object;

    /**
     * @ORM\Column(name="contract_id", type="bigint", nullable=true)
     *
     * @var Contract
     */
    protected $contractId;

    /**
     * @ORM\Column(name="payment_account_id", type="bigint", nullable=true)
     *
     * @var PaymentAccount
     */
    protected $paymentAccountId;

    /**
     * @ORM\Column(name="deposit_account_id", type="bigint", nullable=true)
     *
     * @var DepositAccountEntity
     */
    protected $depositAccountId;

    /**
     * @ORM\Column(type="PaymentType")
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="PaymentStatus")
     *
     * @var string
     */
    protected $status = PaymentStatus::ACTIVE;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     *
     * @var double
     */
    protected $amount;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     *
     * @var double
     */
    protected $total;

    /**
     * @ORM\Column(name="paid_for", type="date", nullable=true)
     *
     * @var \DateTime
     */
    protected $paidFor;

    /**
     * @ORM\Column(name="due_date", type="integer")
     *
     * @var int
     */
    protected $dueDate;

    /**
     * @ORM\Column(name="start_month", type="integer")
     *
     * @var int
     */
    protected $startMonth;

    /**
     * @ORM\Column(name="start_year", type="integer")
     *
     * @var int
     */
    protected $startYear;

    /**
     * @ORM\Column(name="end_month", type="integer", nullable=true)
     *
     * @var int
     */
    protected $endMonth;

    /**
     * @ORM\Column(name="end_year", type="integer", nullable=true)
     *
     * @var int
     */
    protected $endYear;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @ORM\Column(name="close_details", type="array", nullable=true)
     */
    protected $closeDetails;

    /**
     * ORM\Column(name="object_class", type="string", length=255)
     * Exclude from DB schema
     *
     * @var string $objectClass
     */
    protected $objectClass;

    /**
     * ORM\Column(type="bigint")
     * Exclude from DB schema
     *
     * @var integer $version
     */
    protected $version;

    /**
     * ORM\Column(type="array", nullable=true)
     * Exclude from DB schema
     *
     * @var string $data
     */
    protected $data;

    /**
     * ORM\Column(length=255, nullable=true)
     * Exclude from DB schema
     *
     * @var string $data
     */
    protected $username;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @return int
     */
    public function getContractId()
    {
        return $this->contractId;
    }

    /**
     * @param int $contractId
     */
    public function setContractId($contractId)
    {
        $this->contractId = $contractId;
    }

    /**
     * @return int
     */
    public function getPaymentAccountId()
    {
        return $this->paymentAccountId;
    }

    /**
     * @param int $paymentAccountId
     */
    public function setPaymentAccountId($paymentAccountId)
    {
        $this->paymentAccountId = $paymentAccountId;
    }

    /**
     * @return int
     */
    public function getDepositAccountId()
    {
        return $this->depositAccountId;
    }

    /**
     * @param int $depositAccountId
     */
    public function setDepositAccountId($depositAccountId)
    {
        $this->depositAccountId = $depositAccountId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return \DateTime
     */
    public function getPaidFor()
    {
        return $this->paidFor;
    }

    /**
     * @param \DateTime $paidFor
     */
    public function setPaidFor($paidFor)
    {
        $this->paidFor = $paidFor;
    }

    /**
     * @return int
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param int $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return int
     */
    public function getStartMonth()
    {
        return $this->startMonth;
    }

    /**
     * @param int $startMonth
     */
    public function setStartMonth($startMonth)
    {
        $this->startMonth = $startMonth;
    }

    /**
     * @return int
     */
    public function getStartYear()
    {
        return $this->startYear;
    }

    /**
     * @param int $startYear
     */
    public function setStartYear($startYear)
    {
        $this->startYear = $startYear;
    }

    /**
     * @return int
     */
    public function getEndMonth()
    {
        return $this->endMonth;
    }

    /**
     * @param int $endMonth
     */
    public function setEndMonth($endMonth)
    {
        $this->endMonth = $endMonth;
    }

    /**
     * @return int
     */
    public function getEndYear()
    {
        return $this->endYear;
    }

    /**
     * @param int $endYear
     */
    public function setEndYear($endYear)
    {
        $this->endYear = $endYear;
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

    /**
     * @return mixed
     */
    public function getCloseDetails()
    {
        return $this->closeDetails;
    }

    /**
     * @param mixed $closeDetails
     */
    public function setCloseDetails($closeDetails)
    {
        $this->closeDetails = $closeDetails;
    }
}
