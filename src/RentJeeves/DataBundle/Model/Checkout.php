<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Checkout
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     */
    protected $id;

    /**
     * @var \CreditJeeves\DataBundle\Entity\Order
     *
     * @ORM\ManyToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     inversedBy="checkout"
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
     *     type="CheckoutStatus",
     *     options={
     *         "default"="pending"
     *     }
     * )
     */
    protected $status;

    /**
     * @ORM\Column(
     *     type="integer"
     * )
     */
    protected $amount;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
    
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
     * Set status
     *
     * @param string $status
     * @return Unit
     */
    public function setStatus($status = ContractStatus::PENDING)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set Amount
     *
     * @param double $amount
     * @return Checkout
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get amount
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Contract
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
}
