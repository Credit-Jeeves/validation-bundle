<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Order as OrderEntity;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity as Entity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class ProfitStarsTransaction
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
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     inversedBy="profitStarsTransaction",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_number", type="string", nullable=true)
     */
    protected $transactionNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="item_id", type="string", nullable=false, unique=true)
     */
    protected $itemId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $transactionNumber
     */
    public function setTransactionNumber($transactionNumber)
    {
        $this->transactionNumber = $transactionNumber;
    }

    /**
     * @return string
     */
    public function getTransactionNumber()
    {
        return $this->transactionNumber;
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
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param string $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }
}
