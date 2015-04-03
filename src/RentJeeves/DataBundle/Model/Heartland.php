<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Payum2\Heartland\Bridge\Doctrine\Entity\PaymentDetails;
use \DateTime;

/**
 * @ORM\MappedSuperclass
 *
 */
abstract class Heartland extends PaymentDetails
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
     * @var \CreditJeeves\DataBundle\Entity\Order
     *
     * @ORM\ManyToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     inversedBy="heartlands"
     * )
     *
     * @ORM\JoinColumn(
     *     name="order_id",
     *     referencedColumnName="id"
     * )
     */
    protected $order;

    /**
     * @ORM\Column(
     *     name="batch_date",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $batchDate;

    /**
     * @ORM\Column(
     *     name="deposit_date",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $depositDate;

    /**
     * @ORM\Column(
     *      type="TransactionStatus",
     *      options={
     *         "default"="complete"
     *      }
     * )
     */
    protected $status = TransactionStatus::COMPLETE;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount",
     *      inversedBy="transactions"
     * )
     * @ORM\JoinColumn(
     *      name="payment_account_id",
     *      referencedColumnName="id"
     * )
     */
    protected $paymentAccount;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Set Order
     *
     * @param \CreditJeeves\DataBundle\Entity\Order
     * @return contract
     */
    public function setOrder(\CreditJeeves\DataBundle\Entity\Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get Order
     *
     * @return \CreditJeeves\DataBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Heartland
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $batchDate
     */
    public function setBatchDate($batchDate)
    {
        $this->batchDate = $batchDate;
    }

    /**
     * @return mixed
     */
    public function getBatchDate()
    {
        return $this->batchDate;
    }

    /**
     * @param mixed $depositDate
     */
    public function setDepositDate($depositDate)
    {
        $this->depositDate = $depositDate;
    }

    /**
     * @return mixed
     */
    public function getDepositDate()
    {
        return $this->depositDate;
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
     * @param mixed $paymentAccount
     */
    public function setPaymentAccount(PaymentAccount $paymentAccount)
    {
        $this->paymentAccount = $paymentAccount;
    }

    /**
     * @return PaymentAccount|null
     */
    public function getPaymentAccount()
    {
        return $this->paymentAccount;
    }
}
