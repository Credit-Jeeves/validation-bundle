<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Order as OrderEntity;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity as Entity;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 *
 */
abstract class Transaction
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
     * @var OrderEntity
     *
     * @ORM\ManyToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     inversedBy="transactions"
     * )
     * @ORM\JoinColumn(
     *     name="order_id",
     *     referencedColumnName="id"
     * )
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="batch_id",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $batchId;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="transaction_id",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $transactionId;

    /**
     * @var bool
     *
     * @ORM\Column(
     *     name="is_successful",
     *     type="boolean"
     * )
     */
    protected $isSuccessful;

    /**
     * @var string
     * @see \RentJeeves\DataBundle\Enum\TransactionStatus
     *
     * @ORM\Column(
     *     type="TransactionStatus",
     *     options={
     *         "default"="complete"
     *     }
     * )
     */
    protected $status = TransactionStatus::COMPLETE;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="messages",
     *     type="text",
     *     nullable=true
     * )
     */
    protected $messages;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="merchant_name",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $merchantName;

    /**
     * @var double
     *
     * @ORM\Column(
     *     name="amount",
     *     type="decimal",
     *     precision=10,
     *     scale=2
     * )
     */
    protected $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *     name="batch_date",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $batchDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *     name="deposit_date",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $depositDate;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param OrderEntity $order
     */
    public function setOrder(OrderEntity $order)
    {
        $this->order = $order;
    }

    /**
     * @return OrderEntity
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $batchId
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * @return int
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param bool $isSuccessful
     */
    public function setIsSuccessful($isSuccessful)
    {
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * @return bool
     */
    public function getIsSuccessful()
    {
        return $this->isSuccessful;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $merchantName
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->merchantName;
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
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param \DateTime $batchDate
     */
    public function setBatchDate(\DateTime $batchDate)
    {
        $this->batchDate = $batchDate;
    }

    /**
     * @return \DateTime
     */
    public function getBatchDate()
    {
        return $this->batchDate;
    }

    /**
     * @param \DateTime $depositDate
     */
    public function setDepositDate(\DateTime $depositDate = null)
    {
        $this->depositDate = $depositDate;
    }

    /**
     * @return \DateTime
     */
    public function getDepositDate()
    {
        return $this->depositDate;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
