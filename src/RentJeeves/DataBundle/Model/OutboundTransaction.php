<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\OutboundTransactionStatus;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;

/**
 * @ORM\MappedSuperclass
 */
abstract class OutboundTransaction
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
     * @var OrderPayDirect
     *
     * @ORM\ManyToOne(targetEntity="\CreditJeeves\DataBundle\Entity\OrderPayDirect", inversedBy="outboundTransactions")
     * @ORM\JoinColumn(name="order_id",referencedColumnName="id", nullable=false)
     */
    protected $order;

    /**
     * @var int
     *
     * @ORM\Column(name="transaction_id", type="integer", nullable=true)
     */
    protected $transactionId;

    /**
     * @var int
     *
     * @ORM\Column(name="batch_id", type="integer", nullable=true)
     */
    protected $batchId;

    /**
     * @see OutboundTransactionType
     * @ORM\Column(type="OutboundTransactionType")
     */
    protected $type;

    /**
     * @see OutboundTransactionStatus
     * @ORM\Column(type="OutboundTransactionStatus")
     */
    protected $status = OutboundTransactionStatus::SUCCESS;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $message;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deposit_date", type="datetime", nullable=true)
     */
    protected $depositDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="batch_close_date", type="datetime", nullable=true)
     */
    protected $batchCloseDate;

    /**
     * @var string
     *
     * @ORM\Column(name="reversal_description", type="string", nullable=true)
     */
    protected $reversalDescription;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at",type="datetime")
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
     * @return OrderPayDirect
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param OrderPayDirect $order
     */
    public function setOrder(OrderPayDirect $order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param int $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
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
     * @return \DateTime
     */
    public function getDepositDate()
    {
        return $this->depositDate;
    }

    /**
     * @param \DateTime $depositDate
     */
    public function setDepositDate($depositDate)
    {
        $this->depositDate = $depositDate;
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

    /**
     * @return string
     */
    public function getReversalDescription()
    {
        return $this->reversalDescription;
    }

    /**
     * @param string $reversalDescription
     */
    public function setReversalDescription($reversalDescription)
    {
        $this->reversalDescription = $reversalDescription;
    }

    /**
     * @return int
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @param int $batchId
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * @return \DateTime
     */
    public function getBatchCloseDate()
    {
        return $this->batchCloseDate;
    }

    /**
     * @param \DateTime $batchCloseDate
     */
    public function setBatchCloseDate($batchCloseDate)
    {
        $this->batchCloseDate = $batchCloseDate;
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
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
